<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\SelectOptionWasDeleted;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CleanOptionsJobSubscriber implements EventSubscriberInterface
{
    private JobLauncherInterface $jobLauncher;
    private JobInstanceRepository $jobInstanceRepository;
    private TokenStorageInterface $tokenStorage;
    private string $jobName;
    /** @var array<int, SelectOptionWasDeleted> */
    private array $deletedEvents = [];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        string $jobName
    ) {
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->tokenStorage = $tokenStorage;
        $this->jobName = $jobName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SelectOptionWasDeleted::class => 'anOptionWasDeleted',
            StorageEvents::POST_SAVE => 'createCleanOptionsJobIfNeeded',
        ];
    }

    public function anOptionWasDeleted(SelectOptionWasDeleted $selectOptionWasDeleted): void
    {
        $this->deletedEvents[] = $selectOptionWasDeleted;
    }

    public function createCleanOptionsJobIfNeeded(GenericEvent $postSaveEvent): void
    {
        if (!$postSaveEvent->getSubject() instanceof AttributeInterface || $this->deletedEvents === []) {
            return;
        }

        $removedOptionPerColumncode = [];
        foreach ($this->deletedEvents as $event) {
            $removedOptionPerColumncode[$event->columnCode()->asString()][] = $event->optionCode()->asString();
        }

        $configuration = [
            'attribute_code' => $postSaveEvent->getSubject()->getCode(),
            'removed_options_per_column_code' => $removedOptionPerColumncode,
        ];

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        $this->jobLauncher->launch($jobInstance, $user, $configuration);
    }
}
