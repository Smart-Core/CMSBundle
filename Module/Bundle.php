<?php

namespace SmartCore\Bundle\EngineBundle\Module;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

class Bundle extends BaseBundle
{
    /**
     * Получить имя контроллера по умолчанию.
     * Вычисляется как посленяя часть пространства имён.
     * 
     * @return string
     */
    public function getDefaultController()
    {
        // @todo сделать кеширование в АРС.
        $reflector = new \ReflectionClass(get_class($this));
        $namespace = explode('\\', $reflector->getNamespaceName());

        return $namespace[count($namespace) - 1];
    }

    /**
     * Получить имя экшена по умолчанию.
     * 
     * @return string
     */
    public function getDefaultAction()
    {
        return 'index';
    }

    /**
     * Действие при создании ноды.
     */
    public function createNode($node)
    {

    }

    /**
     * Router.
     *
     * @param string $slug
     * @return RouterResponse
     */
    public function router($node, $slug)
    {
        return new RouterResponse(null, 404);
    }
}
