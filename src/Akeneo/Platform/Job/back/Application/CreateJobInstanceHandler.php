<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application;

use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceHandlerInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateJobInstanceHandler implements CreateJobInstanceHandlerInterface
{
    public function __construct(
        private JobInstanceFactory $jobInstanceFactory,
        private JobRegistry $jobRegistry,
        private JobParametersFactory $jobParametersFactory,
        private JobParametersValidator $jobParametersValidator,
        private ValidatorInterface $validator,
        private SaverInterface $jobInstanceSaver,
    ) {
    }

    public function handle(CreateJobInstanceCommand $command): void
    {
        $jobInstance = $this->jobInstanceFactory->createJobInstance($command->type);

        $jobInstance->setConnector($command->connector);
        $jobInstance->setJobName($command->jobName);
        $jobInstance->setCode($command->code);
        $jobInstance->setLabel($command->label);
        $jobInstance->setRawParameters($command->rawParameters);

        $job = $this->getJob($jobInstance);

        $jobParameters = $this->jobParametersFactory->create($job, $jobInstance->getRawParameters());
        $jobInstance->setRawParameters($jobParameters->all());

        $this->validateJob($job, $jobParameters, $jobInstance);

        $this->jobInstanceSaver->save($jobInstance);
    }

    private function getJob(JobInstance $jobInstance): JobInterface
    {
        if (!$this->jobRegistry->has($jobInstance->getJobName())) {
            throw new \RuntimeException('Job does '.$jobInstance->getJobName().' not exists.');
        }

        return $this->jobRegistry->get($jobInstance->getJobName());
    }

    private function validateJob(JobInterface $job, JobParameters $jobParameters, JobInstance $jobInstance): void
    {
        $jobParametersViolations = $this->jobParametersValidator->validate($job, $jobParameters);
        if (0 < $jobParametersViolations->count()) {
            throw new InvalidJobException($jobInstance->getCode(), $job->getName(), $jobParametersViolations);
        }

        $jobInstanceViolations = $this->validator->validate($jobInstance);
        if (0 < $jobInstanceViolations->count()) {
            throw new InvalidJobException($jobInstance->getCode(), $job->getName(), $jobInstanceViolations);
        }
    }
}
