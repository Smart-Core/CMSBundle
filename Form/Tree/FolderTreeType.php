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

        // The "loader" option of DoctrineType was deprecated and will be removed in Symfony 3.0.
        // You should override the getLoader() method instead in a custom type.
        //
        // @todo подумать как можно будет вызывать метод $loader->setOnlyActive($options['only_active']);
        $resolver->setDefaults([
            'choice_label' => 'form_title',
            'class'        => 'CMSBundle:Folder',
            'loader'       => $loader,
            'only_active'  => false,
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
