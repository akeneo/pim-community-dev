<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Job;

use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Purge version of entities
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PurgeVersioning implements TaskletInterface
{
    protected const JOB_CODE = 'versioning_purge';

    protected StepExecution $stepExecution;

    public function __construct(
        private VersionPurgerInterface $versionPurger,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $purgeOptions['batch_size'] = (int)$this->stepExecution->getJobParameters()->get('batch-size');

        $moreThanDays = null !== $this->stepExecution->getJobParameters()->get('more-than-days') ?
            (int)$this->stepExecution->getJobParameters()->get('more-than-days') : null;
        $lessThanDays = null !== $this->stepExecution->getJobParameters()->get('less-than-days') ?
            (int)$this->stepExecution->getJobParameters()->get('less-than-days') : null;

        if (null !== $lessThanDays || null !== $moreThanDays) {
            $purgeOptions['days_number'] = $lessThanDays ?: $moreThanDays;
        }
        $purgeOptions['date_operator'] = null !== $lessThanDays ? '>' : '<';
        $purgeOptions['resource_name'] = $this->stepExecution->getJobParameters()->get('entity');

        $this->versionPurger->purge($purgeOptions);
    }
}
