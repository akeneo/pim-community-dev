<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\CreateJobInstance;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CannotCreateJobInstanceException;
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
    private const IMPORT_TYPE = 'import';
    private const EXPORT_TYPE = 'export';
    private const CREATE_EXPORT_JOB_ACL = 'pim_importexport_export_profile_create';
    private const CREATE_IMPORT_JOB_ACL = 'pim_importexport_import_profile_create';

    public function __construct(
        private readonly JobInstanceFactory $jobInstanceFactory,
        private readonly JobRegistry $jobRegistry,
        private readonly JobParametersFactory $jobParametersFactory,
        private readonly JobParametersValidator $jobParametersValidator,
        private readonly ValidatorInterface $validator,
        private readonly SaverInterface $jobInstanceSaver,
        private readonly SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function handle(CreateJobInstanceCommand $command): void
    {
        $this->checkUserHasPrivilege($command->type);

        $jobInstance = $this->jobInstanceFactory->createJobInstance($command->type);

        $jobInstance->setConnector($command->connector);
        $jobInstance->setJobName($command->jobName);
        $jobInstance->setCode($command->code);
        $jobInstance->setLabel($command->label);
        $jobInstance->setRawParameters($command->rawParameters);
        $jobInstance->setIsVisible($command->isVisible);

        $job = $this->getJob($jobInstance);

        $jobParameters = $this->jobParametersFactory->create($job, $jobInstance->getRawParameters());
        $jobInstance->setRawParameters($jobParameters->all());

        $this->validateJob($job, $jobParameters, $jobInstance);

        $this->jobInstanceSaver->save($jobInstance);
    }

    private function checkUserHasPrivilege(string $jobType): void
    {
        if (
            (self::EXPORT_TYPE === $jobType && !$this->securityFacade->isGranted(self::CREATE_EXPORT_JOB_ACL)) ||
            (self::IMPORT_TYPE === $jobType && !$this->securityFacade->isGranted(self::CREATE_IMPORT_JOB_ACL))
        ) {
            throw CannotCreateJobInstanceException::insufficientPrivilege();
        }
    }

    private function getJob(JobInstance $jobInstance): JobInterface
    {
        if (!$this->jobRegistry->has($jobInstance->getJobName())) {
            throw CannotCreateJobInstanceException::unknownJob($jobInstance->getJobName());
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
