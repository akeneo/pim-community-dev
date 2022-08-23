<?php

namespace Akeneo\Tool\Component\Batch\Updater;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\UpsertRunningUser;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Update a job instance
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceUpdater implements ObjectUpdaterInterface
{
    public function __construct(
        private JobParametersFactory $jobParametersFactory,
        private JobRegistry $jobRegistry,
        private UpsertRunningUser $upsertRunningUser,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $jobInstance
     */
    public function update($jobInstance, array $data, array $options = [])
    {
        if (!$jobInstance instanceof JobInstance) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($jobInstance),
                JobInstance::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($jobInstance, $field, $value);
        }

        $this->upsertRunningUser($jobInstance);
    }

    private function upsertRunningUser(JobInstance $jobInstance): void
    {
        if (!$jobInstance->isScheduled()) {
            return;
        }

        $automation = $jobInstance->getAutomation();
        $this->upsertRunningUser->execute($jobInstance->getCode(), $automation['running_user_groups'] ?? []);
    }

    /**
     * @param JobInstance $jobInstance
     * @param string      $field
     * @param mixed       $data
     */
    protected function setData(JobInstance $jobInstance, $field, $data)
    {
        switch ($field) {
            case 'connector':
                $jobInstance->setConnector($data);
                break;
            case 'alias':
                $jobInstance->setJobName($data);
                break;
            case 'label':
                $jobInstance->setLabel($data);
                break;
            case 'type':
                $jobInstance->setType($data);
                break;
            case 'scheduled':
                $jobInstance->setScheduled($data);
                break;
            case 'automation':
                $jobInstance->setAutomation($data);
                break;
            case 'configuration':
                $job = $this->jobRegistry->get($jobInstance->getJobName());
                /** @var JobParameters $jobParameters */
                $jobParameters = $this->jobParametersFactory->create($job, $data);
                $jobInstance->setRawParameters($jobParameters->all());
                break;
            case 'code':
                $jobInstance->setCode($data);
                break;
        }
    }
}
