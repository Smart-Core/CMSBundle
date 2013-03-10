<?php

namespace SmartCore\Bundle\EngineBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FolderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array('label' => 'Заголовок', 'attr' => array('class' => 'focused')))
            ->add('descr', null, array('label' => 'Описание'))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'SmartCore\Bundle\EngineBundle\Entity\Folder',
        );
    }

    /*
    public function __setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'registration',
        ));
    }
    */

    public function getName()
    {
        return 'engine_folder';
    }
}
