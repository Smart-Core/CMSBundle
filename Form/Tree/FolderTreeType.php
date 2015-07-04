<?php

namespace SmartCore\Bundle\CMSBundle\Form\Tree;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderTreeType extends DoctrineType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $loader = function (Options $options) {
            $loader = $this->getLoader($options['em'], $options['query_builder'], $options['class']);
            $loader->setOnlyActive($options['only_active']);

            return $loader;
        };

        $resolver->setDefaults([
            'only_active' => false,
            'property'    => 'form_title',
            'loader'      => $loader,
            'class'       => 'CMSBundle:Folder',
        ]);
    }

    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new FolderLoader($manager, $queryBuilder, $class);
    }

    public function getName()
    {
        return 'cms_folder_tree';
    }
}
