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

        // Запуск метода init(), который является заменой конструктора для модулей.
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * Установить параметры ноды.
     */
    public function setNode(Node $node)
    {
        $this->node = $node;
        //foreach ($node['params'] as $key => $value) {
        foreach ($node->getParams() as $key => $value) {
            $this->$key = $value;
            /*
            if (isset($this->{$key})) {
                $this->{$key} = $value;
            } else {
                die('Недопустимый параметр: ' . $key);
            }
            */
        }

//        $reflector = new \ReflectionClass($this->node['module_class']);
//        $this->View->setOptions(array('bundle' => $reflector->getShortName() . '::'));

//        $module = $this->container->get('kernel')->getBundle($node->getModule() . 'Module');

//        ld($module);

        $this->View->setOptions(array('bundle' => $node->getModule() . 'Module' . '::'));
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
        $Response = new Response();
        $Response->setStatusCode(404);
        return $Response;
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
     * Получить параметры подключения модуля.
     * 
     * @access public
     * @return array $params
     */
    public function __getParams()
    {
        return array();
    }
    
    /**
     * Получить параметры кеширования модуля.
     * 
     * @access public
     * @return array $params
     */
    public function __getCacheParams($cache_params = array())
    {
        $params = array();
        foreach ($cache_params as $key => $value) {
            $params[$key] = $value;
        }
        return $params;
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
