<?php

namespace SmartCore\Bundle\EngineBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BlockFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('attr' => array('class' => 'focused')))
            ->add('descr')
            ->add('pos')
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'SmartCore\Bundle\EngineBundle\Entity\Block',
        );
    }

    public function getName()
    {
        return 'engine_block';
    }
}
