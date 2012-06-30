<?php

namespace SmartCore\Bundle\EngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModuleBundle extends Bundle
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
}