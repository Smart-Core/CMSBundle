<?php

namespace SmartCore\Bundle\EngineBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class FolderTreeType extends AbstractType
{
    /**
     * Caches created choice lists.
     * @var array
     */
    private $choiceListCache = array();

    protected $container;

    /** @var $folderRepository \Doctrine\ORM\EntityRepository */
    protected $folderRepository;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        //$this->registry = $registry;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::getPropertyAccessor();
    }

    public function setContainer($container)
    {
        $this->container = $container;
        $this->folderRepository = $this->container->get('doctrine')->getRepository('SmartCoreEngineBundle:Folder');
    }

    /**
     * Pass the image url to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parent_folder_id = $form->getParent()->getData()->getParentFolder()->getId();

//        ld($view);

//        ld($options);

        if (array_key_exists('folder_id', $options)) {
            $folders = $this->folderRepository->findAll(
            //array('pos' => 'ASC')
            );

            /** @var $folder Folder */
            foreach ($folders as $folder) {
                $view->vars['choices'][] = new ChoiceView($folder->getId(), $folder->getId(), $folder->getTitle());
            }

            //$view->vars['choices'][] = new ChoiceView('XXX', 'XXX', 'XXX');
        }

//        ld($view->vars);
//        unset($view->vars['choices'][2]);
//        ld($view->vars['choices']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choiceListCache =& $this->choiceListCache;
//        $propertyAccessor = $this->propertyAccessor;
//        $type = $this;

//        $loader = function (Options $options) use ($type) {
//            return $options['data'];
//        };

//        ld($loader);

//        ld($resolver);

        $choice_list = new \Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList(
            array(
                'm',
                'f',
            ),
            array(
                'Male',
                'Female',
            )
        );

//        ld($this);

        $resolver->setDefaults(array(
            'choice_list' => $choice_list
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'folder_tree';
    }
}
