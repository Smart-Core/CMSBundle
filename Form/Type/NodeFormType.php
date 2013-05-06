<?php

namespace SmartCore\Bundle\EngineBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SmartCore\Bundle\EngineBundle\Container;

class NodeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('name', null, array('attr' => array('class' => 'focused')))
            ->add('is_active')
            ->add('is_cached')
            ->add('module', 'choice', array(
                'choices' => Container::get('engine.module_manager')->all(),
                'data' => 'Texter',
                'attr' => array('class' => 'input-block-level'),
            ))
            ->add('folder', 'folder_tree')
            ->add('block', 'entity', array(
                'class' => 'SmartCoreEngineBundle:Block',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('b')->orderBy('b.position', 'ASC');
                },
                'attr' => array('class' => 'input-block-level'),
                'required' => true,
            ))
            ->add('descr')
            ->add('position')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SmartCore\Bundle\EngineBundle\Entity\Node',
        ));
    }

    public function getName()
    {
        return 'engine_node';
    }
}
