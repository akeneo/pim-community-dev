<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Job;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\PurgeConnectionErrorsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\SelectAllAuditableConnectionCodeQuery;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Purge connection errors over 100 and older than a week
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeConnectionErrorsTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'purge_connection_error';

    public function __construct(
        private SelectAllAuditableConnectionCodeQuery $selectAllAuditableConnectionCodes,
        private PurgeConnectionErrorsQuery $purgeErrors
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
    }

    public function execute(): void
    {
        $codes = $this->selectAllAuditableConnectionCodes->execute();
        $this->purgeErrors->execute($codes);
    }
}
