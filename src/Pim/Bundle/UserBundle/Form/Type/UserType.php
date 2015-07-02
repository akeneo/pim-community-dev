<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\UserSubscriber;
use Oro\Bundle\UserBundle\Form\Type\UserType as OroUserType;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Overriden user form to add a custom subscriber
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserType extends OroUserType
{
    /** @var UserPreferencesSubscriber */
    protected $subscriber;

    /** @var RoleRepository  */
    protected $roleRepository;

    /** @var GroupRepository  */
    protected $groupRepository;

    /**
     * @param SecurityContextInterface  $security
     * @param Request                   $request
     * @param UserPreferencesSubscriber $subscriber
     * @param RoleRepository            $roleRepository
     * @param GroupRepository           $groupRepository
     */
    public function __construct(
        SecurityContextInterface $security,
        Request $request,
        UserPreferencesSubscriber $subscriber,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository
    ) {
        parent::__construct($security, $request);

        $this->subscriber = $subscriber;
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $builder->addEventSubscriber(
            new UserSubscriber($builder->getFormFactory(), $this->security)
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
            );
    }
}
