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
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CleanOptionsJobSubscriber implements EventSubscriberInterface
{
    private JobLauncherInterface $jobLauncher;
    private JobInstanceRepository $jobInstanceRepository;
    private TokenStorageInterface $tokenStorage;
    private CreateJobInstanceInterface $createJobInstance;
    private string $jobName;
    /** @var array<string, array<int, SelectOptionWasDeleted>> */
    private array $deletedEventsByAttributeCode = [];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        CreateJobInstanceInterface $createJobInstance,
        string $jobName
    ) {
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->tokenStorage = $tokenStorage;
        $this->createJobInstance = $createJobInstance;
        $this->jobName = $jobName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SelectOptionWasDeleted::class => 'aSelectOptionWasDeleted',
            StorageEvents::POST_SAVE => 'createCleanOptionsJobIfNeeded',
        ];
    }

    public function aSelectOptionWasDeleted(SelectOptionWasDeleted $selectOptionWasDeleted): void
    {
        $this->deletedEventsByAttributeCode[$selectOptionWasDeleted->attributeCode()][] = $selectOptionWasDeleted;
    }

    public function createCleanOptionsJobIfNeeded(GenericEvent $postSaveEvent): void
    {
        $subject = $postSaveEvent->getSubject();
        if (!$subject instanceof AttributeInterface
            || !isset($this->deletedEventsByAttributeCode[$subject->getCode()])
        ) {
            return;
        }

        $removedOptionPerColumncode = [];
        foreach ($this->deletedEventsByAttributeCode[$subject->getCode()] as $event) {
            $removedOptionPerColumncode[$event->columnCode()->asString()][] = $event->optionCode()->asString();
        }

        $configuration = [
            'attribute_code' => $postSaveEvent->getSubject()->getCode(),
            'removed_options_per_column_code' => $removedOptionPerColumncode,
        ];

        $user = $this->tokenStorage->getToken()->getUser();

        $this->jobLauncher->launch($this->getOrCreateJobInstance(), $user, $configuration);
        unset($this->deletedEventsByAttributeCode[$subject->getCode()]);
    }

    private function getOrCreateJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        if (null === $jobInstance) {
            $this->createJobInstance->createJobInstance([
                'code' => $this->jobName,
                'label' => 'Remove the non existing table option values from product and product models',
                'job_name' => $this->jobName,
                'type' => $this->jobName,
            ]);

            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        }

        return $jobInstance;
    }
}
