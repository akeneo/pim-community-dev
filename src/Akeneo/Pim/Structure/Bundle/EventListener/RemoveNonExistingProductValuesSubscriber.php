<?php


declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Structure\Bundle\EventListener;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class RemoveNonExistingProductValuesSubscriber implements EventSubscriberInterface
{
    private const FIELD_ATTRIBUTES_IN_LEVEL = 'attributes_for_this_level';

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var string */
    private $jobName;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        string $jobName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->jobName = $jobName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'launchRemoveNonExistingProductValuesJob',
        ];
    }

    public function launchRemoveNonExistingProductValuesJob(GenericEvent $event): void
    {
        /** @var AttributeOption $attributeOption */
        $attributeOption = $event->getSubject();
        Assert::isInstanceOf($attributeOption, AttributeOption::class);

        $filters = [
            [
                'field' => $attributeOption->getAttribute()->getCode(),
                'operator' => Operators::IN_LIST,
                'value' => [$attributeOption->getCode()],
                'context' => ['ignore_non_existing_values' => true],
            ],
            [
                'field' => self::FIELD_ATTRIBUTES_IN_LEVEL,
                'operator' => Operators::IN_LIST,
                'value' => [$attributeOption->getAttribute()->getCode()],
            ],
        ];

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        $this->jobLauncher->launch($jobInstance, $user, ['filters' => $filters]);
    }
}
