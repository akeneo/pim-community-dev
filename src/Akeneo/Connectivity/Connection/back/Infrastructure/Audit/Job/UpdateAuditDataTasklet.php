<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Job;

use Akeneo\Connectivity\Connection\Infrastructure\Audit\UpdateAuditData;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class UpdateAuditDataTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'update_connectivity_audit_data';

    public function __construct(private UpdateAuditData $updateAuditData)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
    }

    public function execute(): void
    {
        $this->updateAuditData->execute();
    }
}
