<?php

namespace SmartCore\Bundle\CMSBundle\Module;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractNodePropertiesFormType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @param EntityManager   $em
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManager $em, KernelInterface $kernel)
    {
        $this->em       = $em;
        $this->kernel   = $kernel;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    /**
     * @param string $entityName
     *
     * @return array
     */
    protected function getChoicesByEntity($entityName)
    {
        $choices = [];
        foreach ($this->em->getRepository($entityName)->findAll() as $choice) {
            $choices[$choice->getId()] = (string) $choice;
        }

        return $choices;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@CMS/AdminStructure/node_properties_form.html.twig';
    }
}
