<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Form\EventListener\UserSubscriber;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Form\Type\EmailType;

class UserType extends AbstractType
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var bool
     */
    protected $isMyProfilePage;

    /**
     * @param TokenStorageInterface $tokenStorage Token storage
     * @param Request               $request      Request
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Request $request
    ) {

        $this->tokenStorage = $tokenStorage;
        if ($request->attributes->get('_route') == 'oro_user_profile_update') {
            $this->isMyProfilePage = true;
        } else {
            $this->isMyProfilePage = false;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder);
    }


    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // user fields
        $builder->addEventSubscriber(
            new UserSubscriber($builder->getFormFactory(), $this->tokenStorage)
        );
        $this->setDefaultUserFields($builder);
        $builder
            ->add(
                'rolesCollection',
                'entity',
                [
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
                    'required'       => !$this->isMyProfilePage,
                    'read_only'      => $this->isMyProfilePage,
                    'disabled'      => $this->isMyProfilePage,
                ]
            )
            ->add(
                'groups',
                'entity',
                [
                    'class'          => 'OroUserBundle:Group',
                    'property'       => 'name',
                    'multiple'       => true,
                    'expanded'       => true,
                    'required'       => false,
                    'read_only'      => $this->isMyProfilePage,
                    'disabled'       => $this->isMyProfilePage
                ]
            )
            ->add(
                'plainPassword',
                'repeated',
                [
                    'type'           => 'password',
                    'required'       => true,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Re-enter password'],
                ]
            )
            ->add(
                'change_password',
                'oro_change_password'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'Pim\Bundle\UserBundle\Entity\UserInterface',
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
                        ? ['User', 'Default']
                        : ['Registration', 'User', 'Default'];
                },
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'error_mapping'        => [
                    'roles' => 'rolesCollection'
                ],
                'cascade_validation'   => true
            ]
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
                [
                    'required'       => true,
                ]
            )
            ->add(
                'email',
                'email',
                [
                    'label'          => 'E-mail',
                    'required'       => true,
                ]
            )
            ->add(
                'namePrefix',
                'text',
                [
                    'label'          => 'Name prefix',
                    'required'       => false,
                ]
            )
            ->add(
                'firstName',
                'text',
                [
                    'label'          => 'First name',
                    'required'       => true,
                ]
            )
            ->add(
                'middleName',
                'text',
                [
                    'label'          => 'Middle name',
                    'required'       => false,
                ]
            )
            ->add(
                'lastName',
                'text',
                [
                     'label'          => 'Last name',
                     'required'       => true,
                ]
            )
            ->add(
                'nameSuffix',
                'text',
                [
                    'label'          => 'Name suffix',
                    'required'       => false,
                ]
            )
            ->add(
                'birthday',
                'oro_date',
                [
                    'label'          => 'Date of birth',
                    'required'       => false,
                ]
            )
            ->add(
                'imageFile',
                'file',
                [
                    'label'          => 'Avatar',
                    'required'       => false,
                ]
            );
    }
}
