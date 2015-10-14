<?php

namespace SmartCore\Bundle\CMSBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
    /** @var EntityManager */
    protected $em;

    /** @var string */
    protected $user_class;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, $user_class)
    {
        $this->em         = $em;
        $this->user_class = $user_class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = [];
        foreach ($this->em->getRepository('CMSBundle:Role')->findAll() as $role) {
            $roles[$role->getName()] = $role->getName();
        }

        $builder
            ->add('enabled', null,  ['required' => false])
            ->add('username', null, [
                'label' => 'form.username',
                'translation_domain' => 'FOSUserBundle',
                'attr' => ['autofocus' => 'autofocus'],
            ])
            ->add('email', 'email', [
                'label' => 'form.email',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('plainPassword', 'repeated', [
                'type'            => 'password',
                'required'        => false,
                'options'         => ['translation_domain' => 'FOSUserBundle'],
                'first_options'   => ['label' => 'form.new_password'],
                'second_options'  => ['label' => 'form.new_password_confirmation'],
                'invalid_message' => 'fos_user.password.mismatch',
            ])->add('roles', 'choice', [
                'required'        => false,
                'expanded'        => true,
                'multiple'        => true,
                'choices'         => $roles,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->user_class, // 'SmartCore\Bundle\CMSBundle\Model\UserModel',
        ]);
    }

    public function getName()
    {
        return 'smart_core_cms_user';
    }
}
