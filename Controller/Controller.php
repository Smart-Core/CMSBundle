<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use SmartCore\Bundle\EngineBundle\Engine\View;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    /**
     * View object
     * @var View
     */
    protected $View;

    /**
     * Constructor.
     * 
     * Вызывается как parent::__construct(); из дочерних классов.
     * 
     * @todo пересмотреть логику... ненравится мне эта инициализация вида...
     */
    public function __construct()
    {
        $this->initView();
    }
    
    /**
     * NewFunction
     */
    public function initView()
    {
        // По умолчанию устанавливается имя шаблона, как короткое имя контроллера.
        $reflector = new \ReflectionClass(get_class($this));
        
        if (substr($reflector->getShortName(), -10) == 'Controller') {
            $template = substr($reflector->getShortName(), 0, strlen($reflector->getShortName()) - 10);
        } else {
            $template = $reflector->getShortName();
        }

        $this->View = new View(array(
            'template' => strtolower($template),
            'engine' => 'twig',
        ));
    }
    
    /**
     * Магическое обращение к сервисам.
     *
     * @todo убрать.
     */
    public function __get($name)
    {
        if (!is_object($this->container)) {
            throw new \Exception('SmartCore\EngineBundle: Container is not accesible. Service "engine.' . $name . '" fail.');
        }

        if ($name == 'DB') {
            return $this->container->get('engine.db');
        }
    }
    
    /**
     * Обращение к сервисам движка.
     * 
     * @param string $name
     * @return object
     */
    public function engine($name)
    {
        if (!is_object($this->container)) {
            throw new \Exception('SmartCore\EngineBundle: Container is not accesible. Service "engine.' . $name . '" fail.');
        }
        
        if ($this->container->has('engine.' . $name)) {
            return $this->container->get('engine.' . $name);
        } else {
            throw new \Exception('SmartCore\EngineBundle: Service "engine.' . strtolower($name) . '" does not register.');
        }
    }

    public function EM()
    {
        return $this->getDoctrine()->getManager();
    }
    

    public function DQL($dql)
    {
        return $this->EM()->createQuery($dql);
    }
    

    public function getRepo($name)
    {
        return $this->getDoctrine()->getRepository($name);
    }
    
    /**
     * Жесткий редирект.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     */
    public function redirect($url, $status = 302) // @todo переделать!!!
    {
        $str = (empty($url)) ? $_SERVER['REQUEST_URI'] : $url;
        header(sprintf('%s %s %s', $_SERVER['SERVER_PROTOCOL'], $status, Response::$statusTexts[$status]));
        header('Location: ' . $str);
        exit;
    }
}
