<?php

namespace SmartCore\Bundle\EngineBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FolderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::POST_BIND => 'postBind');
    }

    public function postBind(FormEvent $event)
    {
        /** @var $data \SmartCore\Bundle\EngineBundle\Entity\Folder */
        $data = $event->getData();

        $form = $event->getForm();

        $data->setFolderPid($form->get('folder_pid')->getNormData()->getId());
    }
}
