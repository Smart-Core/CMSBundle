<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminModuleController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:module.html.twig', [
            'modules' => $this->get('engine.module_manager')->all(),
        ]);
    }

    /**
     * Управление модулем.
     *
     * @param string $module
     * @param string $slug
     */
    public function manageAction($module, $slug = null)
    {
        return $this->forward("{$module}Module:Admin:index", ['slug' => $slug]);
    }
}
