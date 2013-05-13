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
        $modules = [];
        foreach (Container::get('engine.module')->all() as $module_name => $_dummy) {
            $modules[$module_name] = $module_name;
        }

        $builder
            ->add('module', 'choice', [
                'choices' => $modules,
                'data' => 'Texter',
                'attr' => ['class' => 'input-block-level'],
            ])
            ->add('folder', 'folder_tree')
            ->add('block', 'entity', [
                'class' => 'SmartCoreEngineBundle:Block',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('b')->orderBy('b.position', 'ASC');
                },
                'attr' => ['class' => 'input-block-level'],
                'required' => true,
            ])
            ->add('descr')
            ->add('position')
            ->add('is_active')
            ->add('is_cached')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SmartCore\Bundle\EngineBundle\Entity\Node',
        ]);
    }

    public function getName()
    {
        return 'engine_node';
    }
}
