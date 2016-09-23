<?php

namespace SmartCore\Bundle\CMSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeDefaultPropertiesFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_core_cms_default_node_properties';
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@CMS/AdminStructure/node_properties_form.html.twig';
    }
}
