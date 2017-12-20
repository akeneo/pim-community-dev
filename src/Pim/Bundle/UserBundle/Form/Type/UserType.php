<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\UserSubscriber;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\RoleRepository;
use Pim\Bundle\UserBundle\Event\UserFormBuilderEvent;
use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Overriden user form to add a custom subscriber
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserType extends AbstractType
{
    /** @var UserPreferencesSubscriber */
    protected $subscriber;

    /** @var RoleRepository  */
    protected $roleRepository;

    /** @var GroupRepository  */
    protected $groupRepository;

    /** @var EventDispatcherInterface  */
    protected $eventDispatcher;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var bool */
    protected $isMyProfilePage;

    /**
     * @param TokenStorageInterface     $tokenStorage
     * @param Request                   $request
     * @param UserPreferencesSubscriber $subscriber
     * @param RoleRepository            $roleRepository
     * @param GroupRepository           $groupRepository
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Request $request,
        UserPreferencesSubscriber $subscriber,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->isMyProfilePage = $request->attributes->get('_route') === 'oro_user_profile_update';
        $this->subscriber = $subscriber;
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder);

        $this->eventDispatcher->dispatch(UserFormBuilderEvent::POST_BUILD, new UserFormBuilderEvent($builder));
        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
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
                    'query_builder'  => $this->roleRepository->getAllButAnonymousQB(),
                    'multiple'       => true,
                    'expanded'       => true,
                    'required'       => !$this->isMyProfilePage,
                    'read_only'      => $this->isMyProfilePage,
                    'disabled'       => $this->isMyProfilePage,
                ]
            )
            ->add(
                'groups',
                'entity',
                [
                    'class'          => 'OroUserBundle:Group',
                    'property'       => 'name',
                    'query_builder'  => $this->groupRepository->getAllButDefaultQB(),
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
            )
            ->add('productGridFilters', 'pim_enrich_product_grid_filter_choice', [
                'label'    => 'user.product_grid_filters',
                'multiple' => true,
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => 'Pim\Bundle\UserBundle\Entity\UserInterface',
                'intention'         => 'user',
                'validation_groups' => function ($form) {
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
                    'required' => true,
                ]
            )
            ->add(
                'email',
                'email',
                [
                    'label'    => 'E-mail',
                    'required' => true,
                ]
            )
            ->add(
                'namePrefix',
                'text',
                [
                    'label'    => 'Name prefix',
                    'required' => false,
                ]
            )
            ->add(
                'firstName',
                'text',
                [
                    'label'    => 'First name',
                    'required' => true,
                ]
            )
            ->add(
                'middleName',
                'text',
                [
                    'label'    => 'Middle name',
                    'required' => false,
                ]
            )
            ->add(
                'lastName',
                'text',
                [
                    'label'    => 'Last name',
                    'required' => true,
                ]
            )
            ->add(
                'nameSuffix',
                'text',
                [
                    'label'    => 'Name suffix',
                    'required' => false,
                ]
            )
            ->add(
                'birthday',
                'pim_date',
                [
                    'label'    => 'Date of birth',
                    'required' => false,
                ]
            )
            ->add(
                'imageFile',
                'file',
                [
                    'label'    => 'Avatar',
                    'required' => false,
                ]
            );
    }
}
