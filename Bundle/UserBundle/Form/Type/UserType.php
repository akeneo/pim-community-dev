<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Oro\Bundle\UserBundle\Form\EventListener\UserSubscriber;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Type\EmailType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class UserType extends FlexibleType
{
    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @param FlexibleManager          $flexibleManager flexible manager
     * @param AclManager               $aclManager      ACL manager
     * @param SecurityContextInterface $security        Security context
     */
    public function __construct(
        FlexibleManager $flexibleManager,
        AclManager $aclManager,
        SecurityContextInterface $security
    ) {
        parent::__construct($flexibleManager, '');

        $this->aclManager = $aclManager;
        $this->security   = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        // user fields
        $builder->addEventSubscriber(
            new UserSubscriber($builder->getFormFactory(), $this->aclManager, $this->security)
        );
        $this->setDefaultUserFields($builder);
        $builder
            ->add(
                'rolesCollection',
                'entity',
                array(
                    'label'          => 'Roles',
                    'class'          => 'OroUserBundle:Role',
                    'property'       => 'label',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->where('r.role <> :anon')
                            ->setParameter('anon', User::ROLE_ANONYMOUS);
                    },
                    'multiple'       => true,
                    'expanded'       => true,
                    'required'       => true,
                )
            )
            ->add(
                'groups',
                'entity',
                array(
                    'class'          => 'OroUserBundle:Group',
                    'property'       => 'name',
                    'multiple'       => true,
                    'expanded'       => true,
                    'required'       => false,
                )
            )
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type'           => 'password',
                    'required'       => true,
                    'first_options'  => array('label' => 'Password'),
                    'second_options' => array('label' => 'Re-enter password'),
                )
            )
            ->add(
                'emails',
                'collection',
                array(
                    'type'           => new EmailType(),
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'by_reference'   => false,
                    'prototype'      => true,
                    'prototype_name' => 'tag__name__',
                    'label'          => ' '
                )
            )
            ->add(
                'tags',
                'oro_tag_select'
            )
            ->add(
                'change_password',
                'oro_change_password'
            );
    }

    /**
     * Add entity fields to form builder
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            'collection',
            array(
                'type'          => 'oro_user_user_value',
                'property_path' => 'values',
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array(
                    'data-col'  => 2,
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => $this->flexibleClass,
                'intention'            => 'user',
                'validation_groups'    => function ($form) {
                    if ($form instanceof FormInterface) {
                        $user = $form->getData();
                    } elseif ($form instanceof FormView) {
                        $user = $form->vars['value'];
                    } else {
                        $user = null;
                    }

                    return $user && $user->getId()
                        ? array('User', 'Default')
                        : array('Registration', 'User', 'Default');
                },
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'error_mapping' => array(
                    'roles' => 'rolesCollection'
                ),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_user';
    }

    /**
     * Set user fields
     *
     * @param FormBuilderInterface $builder
     */
    protected function setDefaultUserFields(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'username',
                'text',
                array(
                     'required'       => true,
                )
            )
            ->add(
                'email',
                'email',
                array(
                     'label'          => 'E-mail',
                     'required'       => true,
                )
            )
            ->add(
                'firstName',
                'text',
                array(
                     'label'          => 'First name',
                     'required'       => true,
                )
            )
            ->add(
                'lastName',
                'text',
                array(
                     'label'          => 'Last name',
                     'required'       => true,
                )
            )
            ->add(
                'birthday',
                'oro_date',
                array(
                     'label'          => 'Date of birth',
                     'required'       => false,
                )
            )
            ->add(
                'imageFile',
                'file',
                array(
                     'label'          => 'Avatar',
                     'required'       => false,
                )
            );
    }
}
