<?php

namespace SmartCore\Bundle\CMSBundle\Form\Type;

use SmartCore\Bundle\CMSBundle\Form\Tree\FolderTreeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('description')
            ->add('position')
            ->add('folders', FolderTreeType::class, [
                //'attr'        => ['style' => 'height: 300px;'],
                'only_active' => true,
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'Inherit in folders',
                'required'    => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SmartCore\Bundle\CMSBundle\Entity\Region',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_core_cms_region';
    }
}
