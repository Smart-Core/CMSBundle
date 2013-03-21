<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\FormTypeInterface;
use SmartCore\Bundle\EngineBundle\Entity\Node;

class ModuleManager extends ContainerAware
{
    protected $modules = array();
    protected $initialized = false;
    protected $configFile;
    
    /**
     * Initializes the collection of modules.
     */
    public function initialize()
    {
        if (!$this->initialized) {
            $this->configFile = $this->container->get('kernel')->getRootDir() . '/usr/modules.ini';
            $this->modules = parse_ini_file($this->configFile);
            $this->initialized = true;
        }
    }
        
    /**
     * Получить список всех модулей.
     * 
     * @return array
     */
    public function all()
    {
        return $this->modules;
    }
    
    /**
     * Получить информацию о модуле.
     *
     * @param string $name
     * @return string|null
     */
    public function get($name)
    {
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        } else {
            return null;
        }
    }

    /**
     * Получить форму редактирования параметров подключения модуля.
     *
     * @param Node $node
     * @return FormTypeInterface
     */
    public function getNodePropertiesFormType(Node $node)
    {
        $reflector = new \ReflectionClass($this->get($node->getModule()));
        $form_class_name = '\\' . $reflector->getNamespaceName() . '\Form\Type\NodePropertiesFormType';

        return new $form_class_name;

        // @todo продумать как поступать если класс не найден.
        /*
        if (class_exists($form_class_name)) {
            return new $form_class_name;
        } else {
            return null;
        }
        */
    }

    /**
     * Создание ноды
     *
     * @param Node $node
     */
    public function createNode($node)
    {
        $module = $this->container->get('kernel')->getBundle($node->getModule() . 'Module');

        if (method_exists($module, 'createNode')) {
            $module->createNode($node);
        }
    }
}
