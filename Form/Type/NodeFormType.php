<?php

namespace SmartCore\Bundle\CMSBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Smart\CoreBundle\Form\DataTransformer\HtmlTransformer;
use SmartCore\Bundle\CMSBundle\Container;
use SmartCore\Bundle\CMSBundle\Engine\EngineModule;
use SmartCore\Bundle\CMSBundle\Entity\Node;
use SmartCore\Bundle\CMSBundle\Form\Tree\FolderTreeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeFormType extends AbstractType
{
    /** @var EngineModule */
    protected $cmsModule;

    /**
     * @param EngineModule $engineModule
     */
    public function __construct(EngineModule $engineModule)
    {
        $this->cmsModule = $engineModule;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Запрос списка областей, чтобы в случае отсутствия, была создана дефолтная область.
        // @todo убрать отсюда.
        Container::get('cms.region')->all();

        $modules = [];
        foreach ($this->cmsModule->all() as $module_name => $_dummy) {
            $modules[$module_name] = $module_name;
        }

        $moduleThemes = [];
        foreach ($this->cmsModule->getThemes($options['data']->getModule().'Module') as $theme) {
            $moduleThemes[$theme] = $theme;
        }

        $builder
            ->add('module', ChoiceType::class, [
                'choices' => $modules,
                'data' => 'Texter', // @todo настройку модуля по умолчанию.
            ])
            ->add('folder', FolderTreeType::class)
            ->add('region', EntityType::class, [
                'class' => 'CMSBundle:Region',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('b')->orderBy('b.position', 'ASC');
                },
                'required' => true,
            ])
            ->add('controls_in_toolbar', ChoiceType::class, [
                'choices' => [
                    'Никогда' => Node::TOOLBAR_NO,
                    'Только в собственной папке' => Node::TOOLBAR_ONLY_IN_SELF_FOLDER,
                    //Node::TOOLBAR_ALWAYS => 'Всегда', // @todo
                ],
            ])
            ->add('template', ChoiceType::class, [
                'choices'  => $moduleThemes,
                'required' => false,
                'label'    => 'Тема шаблонов',
            ])
            ->add('description')
            ->add('position')
            ->add('priority')
            ->add($builder->create('code_before')->addViewTransformer(new HtmlTransformer(false)))
            ->add($builder->create('code_after')->addViewTransformer(new HtmlTransformer(false)))
            ->add('is_active', null, ['required' => false])
            ->add('is_cached', null, ['required' => false])
            ->add('is_use_eip', null, ['required' => false])
        ;

        if (empty($moduleThemes)) {
            $builder->remove('template');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Node::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_core_cms_node';
    }
}
