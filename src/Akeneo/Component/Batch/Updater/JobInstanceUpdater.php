<?php

namespace Akeneo\Component\Batch\Updater;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
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
    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /** @var JobRegistry */
    protected $jobRegistry;

    /**
     * @param JobParametersFactory $jobParametersFactory
     * @param JobRegistry          $jobRegistry
     */
    public function __construct(JobParametersFactory $jobParametersFactory, JobRegistry $jobRegistry)
    {
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobRegistry = $jobRegistry;
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
                'Akeneo\Component\Batch\Model\JobInstance'
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($jobInstance, $field, $value);
        }
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
