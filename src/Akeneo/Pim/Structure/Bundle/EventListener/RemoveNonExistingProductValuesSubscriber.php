<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventListener;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\SqlCreateJobInstanceInterface;
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
    private TokenStorageInterface $tokenStorage;
    private JobInstanceRepository $jobInstanceRepository;
    private JobLauncherInterface $jobLauncher;
    private string $jobName;
    private ?SqlCreateJobInstanceInterface $createJobInstance;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        string $jobName,
        SqlCreateJobInstanceInterface $createJobInstance = null // @todo remove nullable on master
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->jobName = $jobName;
        $this->createJobInstance = $createJobInstance;
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

        $configuration = [
            'attribute_code' => $attributeOption->getAttribute()->getCode(),
            'attribute_options' => [$attributeOption->getCode()],
        ];

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->getOrCreateJobInstance();
        $this->jobLauncher->launch($jobInstance, $user, $configuration);
    }

    private function getOrCreateJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        // Create the job instance if the migration was not played
        // @todo remove the whole block on master: the job should exists because we always play migrations
        if (null === $jobInstance && null !== $this->createJobInstance) {
            $this->createJobInstance->createJobInstance([
                'code' => 'remove_non_existing_product_values',
                'label' => 'Remove the non existing values of product and product models',
                'job_name' => 'remove_non_existing_product_values',
                'type' => 'remove_non_existing_product_values',
            ]);

            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        }

        return $jobInstance;
    }
}
