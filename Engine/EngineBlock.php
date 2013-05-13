<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerInterface;

class EngineBlock
{
    use TraitEngine;

    /**
     * Получить все блоки.
     *
     * @return array
     */
    public function all()
    {
        return $this->repository->findBy([], ['position' => 'ASC']);
    }
}
