<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Push a registered job instance to execute into the job execution queue.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishJobToQueue implements PublishJobToQueueInterface
{
    public function __construct(
        private string $kernelEnv,
        private DoctrineJobRepository $jobRepository,
        private ValidatorInterface $validator,
        private JobExecutionQueueInterface $jobExecutionQueue,
        private JobExecutionMessageFactory $jobExecutionMessageFactory,
        private EventDispatcherInterface $eventDispatcher,
        private BatchLogHandler $batchLogHandler,
        private CreateJobExecutionHandlerInterface $createJobExecutionHandler,
    ) {
    }

    public function publish(
        string $jobInstanceCode,
        array $config,
        bool $noLog = false,
        ?string $username = null,
        ?array $emails = [],
    ): JobExecution {
        $jobInstance = $this->getJobInstance($jobInstanceCode);
        $jobExecution = $this->createJobExecutionHandler->createFromJobInstance($jobInstance, $config, $username);
        $options = $this->getOptions($noLog, $emails ?? []);

        $this->batchLogHandler->setSubDirectory((string) $jobExecution->getId());

        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromJobInstance(
            $jobInstance,
            $jobExecution->getId(),
            $options,
        );
        $this->jobExecutionQueue->publish($jobExecutionMessage);

        $this->dispatchJobExecutionEvent($jobExecution);

        return $jobExecution;
    }

    private function getJobInstance(string $jobInstanceCode): JobInstance
    {
        $jobInstance = $this->jobRepository
            ->getJobManager()
            ->getRepository(JobInstance::class)
            ->findOneBy(['code' => $jobInstanceCode]);

        if (null === $jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $jobInstanceCode));
        }

        return $jobInstance;
    }

    private function getOptions(bool $noLog, array $emails): array
    {
        $options = [
            'env' => $this->kernelEnv,
        ];

        if (true === $noLog) {
            $options['no-log'] = true;
        }

        if (0 < count($emails)) {
            $violations = $this->validator->validate(
                $emails,
                new Assert\All([new Assert\Email()]),
            );

            if (0 < $violations->count()) {
                $violationMessages = array_reduce(
                    iterator_to_array($violations),
                    function (string $message, ConstraintViolationInterface $violation) {
                        $message .= sprintf("\n  - %s", $violation->getMessage());
                        return $message;
                    },
                    '',
                );

                throw new \RuntimeException(
                    sprintf(
                        'Emails "%s" are invalid: %s',
                        join(', ', $emails),
                        $violationMessages,
                    )
                );
            }

            $options['email'] = $emails;
        }

        return $options;
    }

    private function dispatchJobExecutionEvent(JobExecution $jobExecution): void
    {
        $event = new JobExecutionEvent($jobExecution);
        $this->eventDispatcher->dispatch($event, EventInterface::JOB_EXECUTION_CREATED);
    }
}
