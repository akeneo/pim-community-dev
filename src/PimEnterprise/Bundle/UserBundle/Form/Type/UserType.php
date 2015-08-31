<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber;
use Pim\Bundle\UserBundle\Form\Type\UserType as BaseUserType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Overridden user form to add field
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class UserType extends BaseUserType
{
    /** @var string */
    protected $class;

    /**
     * @param TokenStorageInterface     $tokenStorage
     * @param Request                   $request
     * @param UserPreferencesSubscriber $subscriber
     * @param RoleRepository            $roleRepository
     * @param GroupRepository           $groupRepository
     * @param string                    $class
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Request $request,
        UserPreferencesSubscriber $subscriber,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        $class
    ) {
        parent::__construct($tokenStorage, $request, $subscriber, $roleRepository, $groupRepository);

        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    protected function setDefaultUserFields(FormBuilderInterface $builder)
    {
        parent::setDefaultUserFields($builder);

        $builder->add(
            'emailNotifications',
            'checkbox',
            [
                'label'    => 'user.email.notifications',
                'required' => false,
            ]
        );

        $builder->add(
            'assetDelayReminder',
            'integer',
            [
                'label'    => 'user.asset_delay_reminder',
                'required' => true,
            ]
        );

        $builder->add(
            'defaultAssetTree',
            'entity',
            [
                'class'         => $this->class,
                'property'      => 'label',
                'select2'       => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->getTreesQB();
                }
            ]
        );
    }
}
