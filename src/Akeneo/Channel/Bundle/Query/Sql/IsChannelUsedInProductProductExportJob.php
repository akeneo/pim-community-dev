<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Query\Sql;

use Akeneo\Channel\Component\Query\IsChannelUsedInProductExportJobInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsChannelUsedInProductProductExportJob implements IsChannelUsedInProductExportJobInterface
{
    private Connection $dbConnection;

    private array $productExportJobNames;

    public function __construct(Connection $dbConnection, array $productExportJobNames)
    {
        $this->dbConnection = $dbConnection;
        $this->productExportJobNames = $productExportJobNames;
    }

    public function execute(string $channelCode): bool
    {
        $isChannelUsedRegex = sprintf('scope[{";:as0-9]+%s', $channelCode);

        $query = <<<SQL
SELECT 1 
FROM akeneo_batch_job_instance
WHERE job_name IN (:jobNames)
    AND raw_parameters REGEXP '$isChannelUsedRegex';
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['jobNames' => $this->productExportJobNames],
            ['jobNames' => Connection::PARAM_STR_ARRAY]
        )->fetchColumn();

        return boolval($result);
    }
}
