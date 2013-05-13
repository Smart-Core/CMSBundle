<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SmartCore\Bundle\EngineBundle\Entity\Block;
use SmartCore\Bundle\EngineBundle\Form\Type\BlockFormType;

class EngineBlock
{
    use TraitEngine;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->constructTrait($container);
        $this->repository = $this->em->getRepository('SmartCoreEngineBundle:Block');
    }

    /**
     * Получить все блоки
     */
    public function all()
    {
        return $this->repository->findBy([], ['position' => 'ASC']);
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm($data = null, array $options = [])
    {
        return $this->container->get('form.factory')->create(new BlockFormType(), $data, $options);
    }

    /**
     * Create block.
     *
     * @return Block
     */
    public function create()
    {
        return new Block();
    }

}