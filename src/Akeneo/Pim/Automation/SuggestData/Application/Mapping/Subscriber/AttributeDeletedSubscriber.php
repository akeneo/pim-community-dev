<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeDeletedSubscriber implements EventSubscriberInterface
{
    /** @var SelectFamilyCodesByAttributeQueryInterface */
    private $familyCodesByAttributeQuery;

    /** @var array */
    private $familyCodes;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param JobLauncherInterface $jobLauncher
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->familyCodesByAttributeQuery = $familyCodesByAttributeQuery;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->familyCodes = [];
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'onPreRemove',
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onPreRemove(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        $this->familyCodes = $this->familyCodesByAttributeQuery->execute($attribute->getCode());
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostRemove(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface || empty($this->familyCodes)) {
            return;
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('suggest_data_remove_attribute_from_mapping');
        $user = $this->tokenStorage->getToken()->getUser();

        foreach ($this->familyCodes as $familyCode) {
            $this->jobLauncher->launch($jobInstance, $user, [
                'pim_attribute_code' => $attribute->getCode(),
                'family_code' => $familyCode,
            ]);
        }
    }
}
