<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use SmartCore\Bundle\EngineBundle\Engine\View;
//use Symfony\Component\HttpFoundation\Response;

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
     * @todo пересмотреть логику... ненравится мне эта инициализация вида...
     */
    public function __construct()
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

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function EM()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function DQL($dql)
    {
        return $this->EM()->createQuery($dql);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepo($name, $persistentManagerName = null)
    {
        return $this->getDoctrine()->getRepository($name, $persistentManagerName);
    }
}
