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

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RemoveNonExistingProductValuesSubscriber implements EventSubscriberInterface
{
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
        $attributeOption = $event->getSubject();
        if (!$attributeOption instanceof AttributeOptionInterface) {
            return;
        }

        $filters = [
            [
                'field' => $attributeOption->getAttribute()->getCode(),
                'operator' => 'IN',
                'value' => [$attributeOption->getCode()],
            ],
        ];

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        $this->jobLauncher->launch($jobInstance, $user, ['filters' => $filters]);
    }
}
