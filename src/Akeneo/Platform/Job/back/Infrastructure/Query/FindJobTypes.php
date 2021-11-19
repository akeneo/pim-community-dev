<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Domain\Query\FindJobTypesInterface;
use Akeneo\Platform\Job\Infrastructure\Registry\NotVisibleJobsRegistry;
use Doctrine\DBAL\Connection;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindJobTypes implements FindJobTypesInterface
{
    private Connection $connection;
    private NotVisibleJobsRegistry $notVisibleJobsRegistry;

    public function __construct(Connection $connection, NotVisibleJobsRegistry $notVisibleJobsRegistry)
    {
        $this->connection = $connection;
        $this->notVisibleJobsRegistry = $notVisibleJobsRegistry; #TODO RAC-1013
    }

    public function visible(): array
    {
        $notVisibleJobsCodes = $this->notVisibleJobsRegistry->getCodes();

        $sql = <<<SQL
SELECT DISTINCT type FROM akeneo_batch_job_instance WHERE code NOT IN (:not_visible_jobs_codes);
SQL;

        $jobTypes = $this->connection->executeQuery(
            $sql,
            [
                'not_visible_jobs_codes' => $notVisibleJobsCodes,
            ],
            [
                'not_visible_jobs_codes' => Connection::PARAM_STR_ARRAY,
            ],
        )->fetchFirstColumn();

        return $jobTypes;
    }
}
