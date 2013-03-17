<?php

namespace SmartCore\Bundle\EngineBundle\Form\Type;

use SmartCore\Bundle\EngineBundle\Form\EventListener\FolderSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FolderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array('attr' => array('class' => 'focused')))
            ->add('uri_part')
            ->add('descr')

            ->add('test', 'folder_tree', array(
                //'folder_id' => $options['data']->getId(),
                'data'      => 'XXX',
                'attr'  => array('class' => 'input-block-level'),
            ))

            /*
            ->add('parent_folder', 'entity', array(
                'class' => 'SmartCoreEngineBundle:Folder',
                'attr'  => array('class' => 'input-block-level'),
            ))
            */
            ->add('pos')
            ->add('is_active')
            ->add('is_file')
            ->add('has_inherit_nodes')
//            ->add('create_datetime', 'text', array('disabled' => true))
//            ->add('permissions', 'text')
//            ->add('lockout_nodes', 'text')
//            ->addEventSubscriber(new FolderSubscriber())
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SmartCore\Bundle\EngineBundle\Entity\Folder',
        ));
    }

    public function getName()
    {
        return 'engine_folder';
    }
}
