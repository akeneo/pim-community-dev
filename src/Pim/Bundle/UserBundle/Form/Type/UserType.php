<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * User form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserType extends AbstractType
{
    /** @var EventSubscriberInterface[] */
    protected $subscribers;

    /** @var DataTransformerInterface[] */
    protected $transformers;

    /** @var RoleRepository  */
    protected $roleRepository;

    /** @var GroupRepository  */
    protected $groupRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var bool */
    protected $isMyProfilePage;

    /**
     * @param Request                    $request
     * @param EventSubscriberInterface[] $subscribers
     * @param DataTransformerInterface[] $transformers
     * @param RoleRepository             $roleRepository
     * @param GroupRepository            $groupRepository
     */
    public function __construct(
        Request $request,
        array $subscribers,
        array $transformers,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository
    ) {
        $this->isMyProfilePage  = $request->attributes->get('_route') === 'pim_user_profile_update';
        $this->subscribers      = $subscribers;
        $this->transformers     = $transformers;
        $this->roleRepository   = $roleRepository;
        $this->groupRepository  = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', [
                'required'       => true,
            ])
            ->add('email', 'email', [
                'label'          => 'E-mail',
                'required'       => true,
            ])
            ->add('namePrefix', 'text', [
                'label'          => 'Name prefix',
                'required'       => false,
            ])
            ->add('firstName', 'text', [
                'label'          => 'First name',
                'required'       => true,
            ])
            ->add('middleName', 'text', [
                'label'          => 'Middle name',
                'required'       => false,
            ])
            ->add('lastName', 'text', [
                'label'          => 'Last name',
                'required'       => true,
            ])
            ->add('nameSuffix', 'text', [
                'label'          => 'Name suffix',
                'required'       => false,
            ])
            ->add('birthday', 'pim_date', [
                'label'          => 'Date of birth',
                'required'       => false,
            ])
            ->add('imageFile', 'file', [
                'label'          => 'Avatar',
                'required'       => false,
            ])
            ->add('rolesCollection', 'entity', [
                'label'          => 'Roles',
                'class'          => 'PimUserBundle:Role',
                'property'       => 'label',
                'query_builder'  => $this->roleRepository->getAllButAnonymousQB(),
                'multiple'       => true,
                'expanded'       => true,
                'required'       => !$this->isMyProfilePage,
                'read_only'      => $this->isMyProfilePage,
                'disabled'       => $this->isMyProfilePage,
            ])
            ->add('groups', 'entity', [
                'class'          => 'PimUserBundle:Group',
                'property'       => 'name',
                'query_builder'  => $this->groupRepository->getAllButDefaultQB(),
                'multiple'       => true,
                'expanded'       => true,
                'required'       => false,
                'read_only'      => $this->isMyProfilePage,
                'disabled'       => $this->isMyProfilePage
            ])
            ->add('plainPassword', 'repeated', [
                'type'           => 'password',
                'required'       => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Re-enter password'],
            ])
            ->add('change_password', 'oro_change_password')
            ->add('productGridFilters', 'pim_datagrid_product_filter_choice', [
                'multiple'       => true,
            ]);

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }

        foreach ($this->transformers as $transformer) {
            $builder->addModelTransformer($transformer);
        }
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
        return 'pim_user_user';
    }
}
