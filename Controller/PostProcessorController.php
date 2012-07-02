<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Engine\Theme;
use SmartCore\Bundle\EngineBundle\Templater\View;

class PostProcessorController extends Controller
{
    public function indexAction($slug)
    {
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
        
        return new Response('PostProcessorController END.');
    }
}