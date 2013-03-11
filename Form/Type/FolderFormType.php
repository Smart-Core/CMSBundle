<?php

namespace SmartCore\Bundle\EngineBundle\Form\Type;

use SmartCore\Bundle\EngineBundle\Form\EventListener\FolderSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FolderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber(new FolderSubscriber())
            ->add('title', null, array('attr' => array('class' => 'focused')))
            ->add('uri_part')
            ->add('descr')
            ->add('folder_pid', 'choice', array(
                'choices'   => array('1' => 'Главная', '2' => 'О Компании'),
                //'required'  => false,
                'attr'      => array('class' => 'input-block-level'),
            ))
            ->add('pos')
            ->add('is_active')
            ->add('is_file')
            ->add('has_inherit_nodes')
//            ->add('create_datetime', 'text', array('disabled' => true))
//            ->add('permissions', 'text')
//            ->add('lockout_nodes', 'text')
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'SmartCore\Bundle\EngineBundle\Entity\Folder',
        );
    }

    public function getName()
    {
        return 'engine_folder';
    }
}
