<?php

namespace Akeneo\UserManagement\Bundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Type\DateType;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Form\Event\UserFormBuilderEvent;
use Akeneo\UserManagement\Bundle\Form\Subscriber\UserPreferencesSubscriber;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserType extends AbstractType
{
    /** @var RoleRepository */
    protected $roleRepository;

    /** @var GroupRepository  */
    protected $groupRepository;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var bool */
    protected $isMyProfilePage;

    /** @var string */
    protected $productGridFilterTypeClassName;

    /** @var array */
    private $eventSubscribers = [];

    /**
     * @param RequestStack              $requestStack
     * @param RoleRepository            $roleRepository
     * @param GroupRepository           $groupRepository
     * @param EventDispatcherInterface  $eventDispatcher
     * @param string                    $productGridFilterTypeClassName
     */
    public function __construct(
        RequestStack $requestStack,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        EventDispatcherInterface $eventDispatcher,
        string $productGridFilterTypeClassName
    ) {
        $this->isMyProfilePage = 'pim_user_profile_update' === $requestStack
                ->getCurrentRequest()
                ->attributes
                ->get('_route');
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->productGridFilterTypeClassName = $productGridFilterTypeClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->eventSubscribers as $eventSubscriber) {
            $builder->addEventSubscriber($eventSubscriber);
        }

        $this->addEntityFields($builder);

        $this->eventDispatcher->dispatch(UserFormBuilderEvent::POST_BUILD, new UserFormBuilderEvent($builder));
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $this->setDefaultUserFields($builder);
        $builder
            ->add(
                'rolesCollection',
                EntityType::class,
                [
                    'label'         => 'pim_user.user.fields.roles',
                    'class'         => Role::class,
                    'choice_label'  => 'label',
                    'query_builder' => $this->roleRepository->getAllButAnonymousQB(),
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => !$this->isMyProfilePage,
                    'attr'          => [
                        'read_only' => $this->isMyProfilePage,
                    ],
                    'disabled'      => $this->isMyProfilePage,
                ]
            )
            ->add(
                'groups',
                EntityType::class,
                [
                    'label'         => 'pim_user.user.fields.groups',
                    'class'         => Group::class,
                    'choice_label'  => 'name',
                    'query_builder' => $this->groupRepository->getAllButDefaultQB(),
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => false,
                    'attr'          => [
                        'read_only' => $this->isMyProfilePage,
                    ],
                    'disabled'      => $this->isMyProfilePage,
                ]
            )
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type'           => PasswordType::class,
                    'required'       => true,
                    'first_options'  => ['label' => 'pim_user.user.fields.password'],
                    'second_options' => ['label' => 'pim_user.user.fields.password_again'],
                ]
            )
            ->add(
                'change_password',
                ChangePasswordType::class
            )
            ->add('productGridFilters', $this->productGridFilterTypeClassName, [
                'label'    => 'pim_user.user.fields.product_grid_filters',
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
                'data_class'        => UserInterface::class,
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
                'constraints'   => new Valid()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
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
                TextType::class,
                [
                    'label'    => 'pim_user.user.fields.username',
                    'required' => true,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label'    => 'pim_user.user.fields.email',
                    'required' => true,
                ]
            )
            ->add(
                'namePrefix',
                TextType::class,
                [
                    'label'    => 'pim_user.user.fields.name_prefix',
                    'required' => false,
                ]
            )
            ->add(
                'firstName',
                TextType::class,
                [
                    'label'    => 'pim_user.user.fields.first_name',
                    'required' => true,
                ]
            )
            ->add(
                'middleName',
                TextType::class,
                [
                    'label'    => 'pim_user.user.fields.middle_name',
                    'required' => false,
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'label'    => 'pim_user.user.fields.last_name',
                    'required' => true,
                ]
            )
            ->add(
                'nameSuffix',
                TextType::class,
                [
                    'label'    => 'pim_user.user.fields.name_suffix',
                    'required' => false,
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label'    => 'pim_user.user.fields.phone',
                    'required' => false,
                ]
            )
            ->add(
                'birthday',
                DateType::class,
                [
                    'label'    => 'pim_user.user.fields.date_of_birth',
                    'required' => false,
                ]
            )
            ->add(
                'imageFile',
                FileType::class,
                [
                    'label'    => 'pim_user.user.fields.avatar',
                    'required' => false,
                ]
            )
            ->add(
                'timezone',
                TimezoneType::class,
                [
                    'label'    => 'pim_user.user.fields.timezone',
                    'required' => true,
                    'choice_label' => function ($timezone, $key) {
                        $currentDateTime = new \DateTime('now', new \DateTimeZone($timezone));

                        return 'UTC' !== $timezone ? sprintf(
                            '%s %s (UTC%s)',
                            $key,
                            $currentDateTime->format('T'),
                            $currentDateTime->format('P')
                        ) : $timezone;
                    },
                ]
            );
    }

    /**
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function addEventSubscribers(EventSubscriberInterface $eventSubscriber): void
    {
        $this->eventSubscribers[] = $eventSubscriber;
    }
}
