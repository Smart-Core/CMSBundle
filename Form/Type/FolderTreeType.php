<?php

namespace SmartCore\Bundle\EngineBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
//use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use SmartCore\Bundle\EngineBundle\Form\Loader\FolderLoader;

class FolderTreeType extends DoctrineType
{
    /**
     * Caches created choice lists.
     * @var array
     */
    private $choiceListCache = array();

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param mixed         $queryBuilder
     * @param string        $class
     * @return EntityLoaderInterface
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new FolderLoader($manager, $queryBuilder, $class);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choiceListCache =& $this->choiceListCache;
        $registry = $this->registry;
        $propertyAccessor = $this->propertyAccessor;
        $type = $this;

        $loader = function (Options $options) use ($type) {
            return $type->getLoader($options['em'], $options['query_builder'], 'SmartCoreEngineBundle:Folder');
        };

        $choiceList = function (Options $options) use (&$choiceListCache, $propertyAccessor) {
            // Support for closures
            $propertyHash = is_object($options['property'])
                ? spl_object_hash($options['property'])
                : $options['property'];

            $choiceHashes = $options['choices'];

            // Support for recursive arrays
            if (is_array($choiceHashes)) {
                // A second parameter ($key) is passed, so we cannot use
                // spl_object_hash() directly (which strictly requires
                // one parameter)
                array_walk_recursive($choiceHashes, function (&$value) {
                    $value = spl_object_hash($value);
                });
            }

            $preferredChoiceHashes = $options['preferred_choices'];

            if (is_array($preferredChoiceHashes)) {
                array_walk_recursive($preferredChoiceHashes, function (&$value) {
                    $value = spl_object_hash($value);
                });
            }

            // Support for custom loaders (with query builders)
            $loaderHash = is_object($options['loader'])
                ? spl_object_hash($options['loader'])
                : $options['loader'];

            // Support for closures
            $groupByHash = is_object($options['group_by'])
                ? spl_object_hash($options['group_by'])
                : $options['group_by'];

            $hash = md5(json_encode(array(
                spl_object_hash($options['em']),
                'SmartCoreEngineBundle:Folder', //$options['class'],
                $propertyHash,
                $loaderHash,
                $choiceHashes,
                $preferredChoiceHashes,
                $groupByHash
            )));

            if (!isset($choiceListCache[$hash])) {
                $choiceListCache[$hash] = new EntityChoiceList(
                    $options['em'],
                    'SmartCoreEngineBundle:Folder', //$options['class'],
                    $options['property'],
                    $options['loader'],
                    $options['choices'],
                    $options['preferred_choices'],
                    $options['group_by'],
                    $propertyAccessor
                );
            }

            return $choiceListCache[$hash];
        };

        $emNormalizer = function (Options $options, $em) use ($registry) {
            /* @var ManagerRegistry $registry */
            if (null !== $em) {
                return $registry->getManager($em);
            }

            $em = $registry->getManagerForClass('SmartCoreEngineBundle:Folder'); //$em = $registry->getManagerForClass($options['class']);

            if (null === $em) {
                throw new Exception(sprintf(
                    'Class "%s" seems not to be a managed Doctrine entity. ' .
                        'Did you forget to map it?',
                    'SmartCoreEngineBundle:Folder' //$options['class']
                ));
            }

            return $em;
        };

        $resolver->setDefaults(array(
            'em'                => null,
            'property'          => 'form_title', // null
            'query_builder'     => null,
            'loader'            => $loader,
            'choices'           => null,
            'choice_list'       => $choiceList,
            'group_by'          => null,
            'attr'              => array('class' => 'input-block-level'),
        ));

        //$resolver->setRequired(array('class'));

        $resolver->setNormalizers(array(
            'em' => $emNormalizer,
        ));

        $resolver->setAllowedTypes(array(
            'loader' => array('null', 'Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface'),
        ));
    }

    public function getName()
    {
        return 'folder_tree';
    }
}
