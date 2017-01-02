<?php

namespace SmartCore\Bundle\CMSBundle\Controller;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Smart\CoreBundle\Controller\Controller;
use SmartCore\Bundle\CMSBundle\Form\Type\RoleFormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminUserController extends Controller
{
    /**
     * Список всех пользователей.
     *
     * @return Response
     *
     * @todo постраничность
     */
    public function indexAction()
    {
        return $this->render('@CMS/AdminUser/index.html.twig', [
            'users' => $this->get('fos_user.user_manager')->findUsers(),
        ]);
    }

    /**
     * На основе \FOS\UserBundle\Controller\RegistrationController::registerAction.
     *
     * @param Request $request
     *
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->get('cms.form.registration.admin.factory')->createForm();
        $form->setData($user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $event = new FormEvent($form, $request);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->get('router')->generate('cms_admin_user');
                    $response = new RedirectResponse($url);
                }

                $this->addFlash('success', 'Новый пользователь <b>'.$user->getUsername().'</b> создан.');

                return $response;
            }
        }

        return $this->render('@CMS/AdminUser/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * На основе \FOS\UserBundle\Controller\ProfileController::editAction.
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserBy(['id' => $id]);
        if (!is_object($user) || !$user instanceof UserInterface) {
            return $this->redirect($this->generateUrl('cms_admin_user'));
        }

        $form = $this->get('cms.form.profile.admin.factory')->createForm();
        $form->setData($user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $event = new GetResponseUserEvent($user, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

                $userManager->updateUser($user);

                $event = new GetResponseUserEvent($user, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, $event);

                $this->addFlash('success', 'Данные пользовалеля <b>'.$user->getUsername().'</b> обновлены.');

                return $this->redirect($this->generateUrl('cms_admin_user'));
            }
        }

        return $this->render('@CMS/AdminUser/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     */
    public function rolesAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(RoleFormType::class);
        $form->remove('position');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $role = $form->getData();
                $this->persist($role, true);

                $this->addFlash('success', "Роль <b>$role</b> создана.");

                return $this->redirect($this->generateUrl('cms_admin_user_roles'));
            }
        }

        return $this->render('@CMS/AdminUser/roles.html.twig', [
            'form'  => $form->createView(),
            'roles' => $em->getRepository('CMSBundle:Role')->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function roleEditAction(Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $role = $em->find('CMSBundle:Role', $id);

        $form = $this->createForm(RoleFormType::class, $role);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($role, true);

                $this->addFlash('success', 'Роль обновлена.');

                return $this->redirect($this->generateUrl('cms_admin_user_roles'));
            }
        }

        return $this->render('@CMS/AdminUser/role_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
