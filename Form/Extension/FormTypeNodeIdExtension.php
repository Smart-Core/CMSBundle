<?php

namespace SmartCore\Bundle\EngineBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
//use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;

class FormTypeNodeIdExtension extends AbstractTypeExtension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->add('__cmf_node_id', 'hidden', ['data' => microtime()]);
//        ld(567);

//        if (!$options['captcha_enabled']) {
//            return;
//        }

        // you may add fields or event listeners to the form here
    }

    /*
    public function __getDefaultOptions(array $options)
    {
        return array(
            'captcha_enabled' => false, // we don't want all forms to have a captcha field
            'captcha_field_name' => '_captcha'
        );
    }
    */

    public function getExtendedType()
    {
        return 'form'; // extend the general "form" type, not some specific form
    }
}
