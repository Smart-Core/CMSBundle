<?php
namespace SmartCore\Bundle\EngineBundle\Form\Extension;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SmartCore\Bundle\EngineBundle\Entity\Folder;

class FolderTreeExtension extends AbstractTypeExtension
{
    protected $container;

    /** @var $folderRepository \Doctrine\ORM\EntityRepository */
    protected $folderRepository;

    public function setContainer($container)
    {
        $this->container = $container;
        $this->folderRepository = $this->container->get('doctrine')->getRepository('SmartCoreEngineBundle:Folder');
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'folder_tree';
    }

    /**
     * Add the folder_id option
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('folder_id'));
    }

    public function __buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = $builder->getOptions();
//        ld($builder);
//        ld($builder->getData());
        $options['choices']['XXX'] = 'XXX';
        $options['choice_list'] = new \Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList(
            array('XXX' => 'XXX')
        );
//        ld($options['choice_list']);
//        ld($options);
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

        if (array_key_exists('folder_id__', $options)) {
            $folders = $this->folderRepository->findAll(
                //array('pos' => 'ASC')
            );

//            ld($folders);
            /** @var $folder Folder */
            foreach ($folders as $folder) {
                $view->vars['choices'][] = new ChoiceView($folder->getId(), $folder->getId(), $folder->getTitle());
            }

            $view->vars['choices'][] = new ChoiceView('XXX', 'XXX', 'XXX');
        }

//        ld($view->vars);
//        ld($view->vars['choices']);
    }
}
