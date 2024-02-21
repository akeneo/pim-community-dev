<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Job;

use Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence\PurgeAuditErrorQuery;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @author    Sébastien DESTRÉ <sebastien.destre@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Purge audit_error table
 */
class PurgeAuditErrorTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'connectivity_audit_purge_error';

    public function __construct(private PurgeAuditErrorQuery $purgeAuditErrorsQuery)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
    }

    public function execute(): void
    {
        $before = new \DateTimeImmutable('now - 10 days', new \DateTimeZone('UTC'));
        $before = $before->setTime((int) $before->format('H'), 0, 0);

        $this->purgeAuditErrorsQuery->execute($before);
    }
}
