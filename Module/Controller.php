<?php

namespace SmartCore\Bundle\EngineBundle\Module;

use SmartCore\Bundle\EngineBundle\Controller\Controller as BaseController;
use SmartCore\Bundle\EngineBundle\Container;
use SmartCore\Bundle\EngineBundle\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use SmartCore\Bundle\EngineBundle\Entity\Node;

abstract class Controller extends BaseController
{
    /**
     * Edit-In-Place
     * @var bool
     */
    private $_eip = false;

    /**
     * Действие по умолчанию.
     * @var string|false
     */
    protected $default_action = false;
    
    /**
     * Фронтальные элементы управления для всего модуля.
     * @var array|false
     */
    protected $frontend_controls = false;
    
    /**
     * Фронтальные элементы управления для внутренних элементов модуля.
     * @var array|false
     */
    protected $frontend_inner_controls = false;
    
    /**
     * Свойства ноды.
     * @var Node
     */
    protected $node;
    
    /**
     * Базовый конструктор. Модули используют в качестве конструктора метод init();
     * 
     * @param int $node_id
     */
    final public function __construct()
    {
        parent::__construct();

        $this->container = Container::getContainer();

        // @todo возможно есть смысл отказаться от такого метода...
        // Запуск метода init(), который является заменой конструктора для модулей.
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    public function setEip($eip)
    {
        $this->_eip = $eip;
    }

    public function getEip()
    {
        return $this->_eip;
    }

    /**
     * Установить параметры ноды.
     */
    public function setNode(Node $node)
    {
        $this->node = $node;
        foreach ($node->getParams() as $key => $value) {
            $this->$key = $value;
        }

        //$reflector = new \ReflectionClass($this->node['module_class']);
        //$this->View->setOptions(array('bundle' => $reflector->getShortName() . '::'));

        $this->View->setOptions(['bundle' => $node->getModule() . 'Module' . '::']);
    }

    /**
     * Получение данных из кеша.
     *
     * @return string
     */
    protected function getCache($key, $default = null)
    {
        if (!$this->node->getIsCached()) {
            return $default;
        }

        $dir = $this->get('kernel')->getCacheDir() . '/smart_core/node/' . $this->node->getId() . '/';
        if (file_exists($dir . $key)) {
            return unserialize(file_get_contents($dir . $key));
        } else {
            return $default;
        }
    }

    /**
     * Поместить данные в кеш.
     *
     * @return string
     */
    protected function setCache($key, $value)
    {
        if (!$this->node->getIsCached()) {
            return false;
        }

        $dir = $this->get('kernel')->getCacheDir() . '/smart_core/node/' . $this->node->getId() . '/';

        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create the %s directory', $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf('Unable to write in the %s directory', $dir));
        }

        /** @see \Symfony\Component\Config\ConfigCache */
        file_put_contents($dir . $key, serialize($value));

        return true;
    }

    /**
     * Проверка, включен ли тулбар.
     *
     * @return bool
     */
    public function isToolbar()
    {
        return true;
    }

    /**
     * Проверка, включен ли режим Edit-in-place.
     *
     * @return bool
     */
    public function isEip()
    {
        return true;
    }

    /**
     * Обработчик POST данных.
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        return new Response('Method postAction is not exist', 404);
    }

    // @todo пересмотреть нижеописанные методы!
    // -------------------------------------------------------------------------------------

    /**
     * Ajax.
     *
     * @param string $uri_path - часть URI, адресованная модулю.
     * @return ?
     */
    public function __ajax($uri_path)
    {
        return null;
    }
    
    /**
     * Метод управления модулем.
     *
     * @param string $uri_path
     * @return array
     */
    public function __admin($uri_path)
    {
        return false;
    }

    /**
     * Вызывается при создании ноды.
     * 
     * @access public
     * @return array $params
     */
    public function __createNode()
    {
        $params = $this->Node->getDefaultParams();
        return empty($params) ? 'NULL' : $params;
    }
    
    /**
     * Метод-заглушка, для модулей, которые не имеют фронт администрирования. 
     * Возвращает пустой массив или null или 0, следовательно движок ничего не отображает.
     * 
     * @access public
     * @returns array|false
     * 
     * @todo определиться какое значение лучше возвращать 0 или false.
     */
    public function __getFrontControls()
    {
        return $this->frontend_controls;
    }
    
    /**
     * Внутренние элменты управления ноды.
     * 
     * @access public
     * @returns array|false
     */
    public function __getFrontControlsInner()
    {
        return $this->frontend_inner_controls;
    }
    
    /**
     * Действие по умолчанию.
     * 
     * @access public
     * @returns string|false
     */
    public function __getFrontControlsDefaultAction()
    {
        return $this->default_action;
    }
    
    /**
     * Выполнение задач по расписанию.
     *
     * @access public
     * @return bool|null
     */
    public function __cron()
    {
        return null;
    }
}
