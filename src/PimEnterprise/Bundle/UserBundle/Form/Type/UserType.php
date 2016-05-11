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

use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber as CEUserPreferencesSubscriber;
use Pim\Bundle\UserBundle\Form\Type\UserType as BaseUserType;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use PimEnterprise\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber as EEUserPreferencesSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    /** @var EEUserPreferencesSubscriber */
    protected $eeSubscriber;

    /** @var TranslatedLabelsProviderInterface */
    protected $categoryProvider;

    /**
     * @param TokenStorageInterface             $tokenStorage
     * @param Request                           $request
     * @param CEUserPreferencesSubscriber       $ceSubscriber
     * @param RoleRepository                    $roleRepository
     * @param GroupRepository                   $groupRepository
     * @param EventDispatcherInterface          $eventDispatcher
     * @param EEUserPreferencesSubscriber       $eeSubscriber
     * @param TranslatedLabelsProviderInterface $categoryProvider
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Request $request,
        CEUserPreferencesSubscriber $ceSubscriber,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        EventDispatcherInterface $eventDispatcher,
        EEUserPreferencesSubscriber $eeSubscriber,
        TranslatedLabelsProviderInterface $categoryProvider
    ) {
        parent::__construct(
            $tokenStorage,
            $request,
            $ceSubscriber,
            $roleRepository,
            $groupRepository,
            $eventDispatcher
        );

        $this->eeSubscriber     = $eeSubscriber;
        $this->categoryProvider = $categoryProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber($this->eeSubscriber);
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
            'light_entity',
            [
                'select2'    => true,
                'repository' => $this->categoryProvider
            ]
        );
    }
}
