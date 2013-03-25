<?php
namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;

class Block extends ContainerAware
{
    /**
     * Получить список всех блоков.
     * 
     * @return array
     */
    public function all()
    {
        return $this->container->get('doctrine')->getRepository('SmartCoreEngineBundle:Block')->findBy(
            array(),
            array('position' => 'ASC')
        );
    }
}
