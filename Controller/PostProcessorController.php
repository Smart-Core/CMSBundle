<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PostProcessorController extends Controller
{
    public function indexAction(Request $request, $slug)
    {
        $data = $request->request->all();
        $node_id = null;
        foreach ($data as $key => $value) {
            if ($key == '_cmf_node_id') {
                $node_id = $data['_cmf_node_id'];
            }

            if (array_key_exists('_cmf_node_id', $value)) {
                $node_id = $data[$key]['_cmf_node_id'];
                unset($data[$key]['_cmf_node_id']);
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
            return $this->forward('SmartCoreEngineBundle:NodeMapper:index', array('slug' => $slug));
        }

        if ($request->request->get('submit') === 'cancel') {
            return new RedirectResponse($request->server->get('HTTP_REFERER') . '#');
        }

        $node_id = $request->request->getInt('_node_id');

        $response = $this->forward("$node_id:Texter:post");
        $json_response = json_decode($response->getContent());
        if ($json_response->status === 'OK') {
            if (isset($json_response->message)) {
                $request->getSession()->getFlashBag()->add('texter_info', $json_response->message);
            }

            return new RedirectResponse($request->server->get('HTTP_REFERER') . '#');
        }

        ob_start();
        print_r($_POST);
        $dump = ob_get_clean();

        return new Response("<h1>PostProcessorController</h1><pre>$dump</pre>");
    }
}
