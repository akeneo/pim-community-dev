<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Pim\Bundle\UIBundle\Form\Type\DateType;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\RoleRepository;
use Pim\Bundle\UserBundle\Form\Event\UserFormBuilderEvent;
use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber;
use Pim\Component\User\Model\Group;
use Pim\Component\User\Model\Role;
use Pim\Component\User\Model\UserInterface;
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
    private $eventSubscribers;

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
                    'label'         => 'Roles',
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
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Re-enter password'],
                ]
            )
            ->add(
                'change_password',
                ChangePasswordType::class
            )
            ->add('productGridFilters', $this->productGridFilterTypeClassName, [
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
                    'required' => true,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label'    => 'E-mail',
                    'required' => true,
                ]
            )
            ->add(
                'namePrefix',
                TextType::class,
                [
                    'label'    => 'Name prefix',
                    'required' => false,
                ]
            )
            ->add(
                'firstName',
                TextType::class,
                [
                    'label'    => 'First name',
                    'required' => true,
                ]
            )
            ->add(
                'middleName',
                TextType::class,
                [
                    'label'    => 'Middle name',
                    'required' => false,
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'label'    => 'Last name',
                    'required' => true,
                ]
            )
            ->add(
                'nameSuffix',
                TextType::class,
                [
                    'label'    => 'Name suffix',
                    'required' => false,
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label'    => 'Phone',
                    'required' => false,
                ]
            )
            ->add(
                'birthday',
                DateType::class,
                [
                    'label'    => 'Date of birth',
                    'required' => false,
                ]
            )
            ->add(
                'imageFile',
                FileType::class,
                [
                    'label'    => 'Avatar',
                    'required' => false,
                ]
            )
            ->add(
                'timezone',
                TimezoneType::class,
                [
                    'label'    => 'user.timezone',
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
