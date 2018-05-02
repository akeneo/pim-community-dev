<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Load raw parameters of a job execution in order to inject it as a value object JobParameters,
 * when loading the object with Doctrine.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadJobParametersListener
{
    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /**
     * @param JobParametersFactory $jobParametersFactory
     */
    public function __construct(JobParametersFactory $jobParametersFactory)
    {
        $this->jobParametersFactory = $jobParametersFactory;
    }

    /**
     * Load the raw parameters of the job into the value object JobParameters.
     *
     * @param JobExecution       $jobExecution
     * @param LifecycleEventArgs $event
     */
    public function postLoad(JobExecution $jobExecution, LifecycleEventArgs $event): void
    {
        $jobParameters = $this->jobParametersFactory->createFromRawParameters($jobExecution);
        $jobExecution->setJobParameters($jobParameters);
    }
}
