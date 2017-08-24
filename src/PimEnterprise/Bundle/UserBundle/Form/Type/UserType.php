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

use Pim\Bundle\EnrichBundle\Form\Type\LightEntityType;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\RoleRepository;
use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber as CEUserPreferencesSubscriber;
use Pim\Bundle\UserBundle\Form\Type\UserType as BaseUserType;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use PimEnterprise\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber as EEUserPreferencesSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @param RequestStack                      $requestStack
     * @param CEUserPreferencesSubscriber       $ceSubscriber
     * @param RoleRepository                    $roleRepository
     * @param GroupRepository                   $groupRepository
     * @param EventDispatcherInterface          $eventDispatcher
     * @param EEUserPreferencesSubscriber       $eeSubscriber
     * @param TranslatedLabelsProviderInterface $categoryProvider
     * @param string                            $productGridFilterTypeClassName
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        CEUserPreferencesSubscriber $ceSubscriber,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        EventDispatcherInterface $eventDispatcher,
        EEUserPreferencesSubscriber $eeSubscriber,
        TranslatedLabelsProviderInterface $categoryProvider,
        string $productGridFilterTypeClassName
    ) {
        parent::__construct(
            $tokenStorage,
            $requestStack,
            $ceSubscriber,
            $roleRepository,
            $groupRepository,
            $eventDispatcher,
            $productGridFilterTypeClassName
        );

        $this->eeSubscriber = $eeSubscriber;
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
            CheckboxType::class,
            [
                'label'    => 'user.email.notifications',
                'required' => false,
            ]
        );

        $builder->add(
            'assetDelayReminder',
            IntegerType::class,
            [
                'label'    => 'user.asset_delay_reminder',
                'required' => true,
            ]
        );

        $builder->add(
            'defaultAssetTree',
            LightEntityType::class,
            [
                'select2'    => true,
                'repository' => $this->categoryProvider
            ]
        );
    }
}
