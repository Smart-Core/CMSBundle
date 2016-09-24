<?php

namespace SmartCore\Bundle\CMSBundle\Form\Type;

use SmartCore\Bundle\CMSBundle\Entity\Folder;
use SmartCore\Bundle\CMSBundle\Form\Tree\FolderTreeType;
use SmartCore\Bundle\SeoBundle\Form\Type\MetaFormType;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderFormType extends AbstractType
{
    use ContainerAwareTrait;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $finder = new Finder();
        $finder->files()->sortByName()->depth('== 0')->name('*.html.twig')->in($this->container->get('kernel')->getBundle('SiteBundle')->getPath().'/Resources/views/');

        $templates = ['' => ''];
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $name = str_replace('.html.twig', '', $file->getFilename());
            $templates[$name] = $name;
        }

        $routedNodes = ['' => ''];
        foreach ($this->container->get('cms.node')->findInFolder($options['data']) as $node) {
            if (!$this->container->has('cms.router_module.'.$node->getModule())) {
                continue;
            }

            $nodeTitle = $node->getId().': '.$node->getModule();

            if ($node->getDescription()) {
                $nodeTitle .= ' ('.$node->getDescription().')';
            }

            $routedNodes[$node->getId()] = $nodeTitle;
        }

        $builder
            ->add('title', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('uri_part')
            ->add('description')
            ->add('parent_folder', FolderTreeType::class)
            ->add('router_node_id', ChoiceType::class, [
                'choices'  => $routedNodes,
                'required' => false,
            ])
            ->add('position')
            ->add('is_active', null, ['required' => false])
            ->add('is_file',   null, ['required' => false])
            ->add('template_inheritable', ChoiceType::class, [
                'choices'  => $templates,
                'required' => false,
            ])
            ->add('template_self', ChoiceType::class, [
                'choices'  => $templates,
                'required' => false,
            ])
            ->add('meta', MetaFormType::class, ['label' => 'Meta tags'])
            //->add('permissions', 'text')
            //->add('lockout_nodes', 'text')
            //->addEventSubscriber(new FolderSubscriber())
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_core_cms_folder';
    }
}
