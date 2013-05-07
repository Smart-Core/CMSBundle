<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PostProcessorController extends Controller
{
    public function indexAction(Request $request, $slug)
    {
        if ($request->request->get('submit') === 'cancel') {
            return new RedirectResponse($request->server->get('HTTP_REFERER') . '#');
        }

        // Получение $node_id
        $data = $request->request->all();
        $node_id = null;
        foreach ($data as $key => $value) {
            if ($key == '_node_id') {
                $node_id = $data['_node_id'];
                unset($data['_node_id']);
                break;
            }

            if (array_key_exists('_node_id', $value)) {
                $node_id = $data[$key]['_node_id'];
                unset($data[$key]['_node_id']);
                break;
            }
        }
        foreach ($data as $key => $value) {
            $request->request->set($key, $value);
        }

        // @todo УБРАТЬ, это сейчас тут тесты с регистрацией...
        if (isset($_POST['fos_user_registration_form']) or
            isset($_POST['fos_user_profile_form']) or
            isset($_POST['fos_user_resetting_form']) or
            isset($_POST['fos_user_change_password_form']) or 
            $this->container->get('request')->getBaseUrl() . '/' . $slug === $this->container->get('router')->generate('fos_user_resetting_send_email') or
            $this->container->get('request')->getBaseUrl() . '/' . $slug === $this->container->get('router')->generate('fos_user_resetting_check_email')
        ) {
            return $this->forward('SmartCoreEngineBundle:NodeMapper:index', ['slug' => $slug]);
        }

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return new JsonResponse([
                'status' => 'INVALID',
                'message' => 'Access denied',
            ], 403);
        }

        $module_name = $this->get('engine.node_manager')->get($node_id)->getModule();

        return $this->forward("{$node_id}:{$module_name}:post");

        /*
        $router_data = $this->get('engine.folder')->router($request->getPathInfo());
        $nodes_list  = $this->get('engine.node_manager')->buildNodesList($router_data);
        ld($nodes_list);
        ld($node_id);
        */
    }
}
