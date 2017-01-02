<?php

namespace SmartCore\Bundle\CMSBundle\Controller;

use Smart\CoreBundle\Controller\Controller;
use SmartCore\Bundle\MediaBundle\Entity\Collection;
use SmartCore\Bundle\MediaBundle\Entity\Storage;
use SmartCore\Bundle\MediaBundle\Form\Type\CollectionFormType;
use SmartCore\Bundle\MediaBundle\Form\Type\StorageFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMediaLibraryController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $collections = $em->getRepository('SmartMediaBundle:Collection')->findAll();
        $storages    = $em->getRepository('SmartMediaBundle:Storage')->findAll();

        return $this->render('@CMS/AdminMediaLibrary/index.html.twig', [
            'collections'   => $collections,
            'storages'      => $storages,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createStorageAction(Request $request)
    {
        $form = $this->createForm(StorageFormType::class, new Storage('/_media'));
        $form->add('create', SubmitType::class, ['attr' => ['class' => 'btn btn-success']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin_config_media');
            }

            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Хранилище создано');

                return $this->redirectToRoute('cms_admin_config_media');
            }
        }

        return $this->render('@CMS/AdminMediaLibrary/create_storage.html.twig', [
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function editStorageAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $storage = $em->find('SmartMediaBundle:Storage', $id);

        if (empty($storage)) {
            return $this->redirectToRoute('cms_admin_config_media');
        }

        $form = $this->createForm(StorageFormType::class, $storage);
        $form->add('update', SubmitType::class, ['attr' => ['class' => 'btn btn-success']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin_config_media');
            }

            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Хранилище обновлено');

                return $this->redirectToRoute('cms_admin_config_media');
            }
        }

        return $this->render('@CMS/AdminMediaLibrary/edit_storage.html.twig', [
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createCollectionAction(Request $request)
    {
        $collection = new Collection('/new');
        $collection->setTitle('Новая коллекция');

        $form = $this->createForm(CollectionFormType::class, $collection);
        $form->add('create', SubmitType::class, ['attr' => ['class' => 'btn btn-success']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin_config_media');
            }

            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Коллекция создана');

                return $this->redirectToRoute('cms_admin_config_media');
            }
        }

        return $this->render('@CMS/AdminMediaLibrary/create_collection.html.twig', [
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function editCollectionAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $collection = $em->find('SmartMediaBundle:Collection', $id);

        if (empty($collection)) {
            return $this->redirectToRoute('cms_admin_config_media');
        }

        $form = $this->createForm(CollectionFormType::class, $collection);
        $form->add('update', SubmitType::class, ['attr' => ['class' => 'btn btn-success']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin_config_media');
            }

            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Коллекция обновлена');

                return $this->redirectToRoute('cms_admin_config_media');
            }
        }

        return $this->render('@CMS/AdminMediaLibrary/edit_collection.html.twig', [
            'form'   => $form->createView(),
        ]);
    }
}
