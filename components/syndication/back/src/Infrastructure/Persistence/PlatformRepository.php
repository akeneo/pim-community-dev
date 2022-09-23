<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Persistence;

use Akeneo\Platform\Syndication\Domain\Model\Platform;
use Akeneo\Platform\Syndication\Domain\Model\PlatformFamily;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\PlatformRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class PlatformRepository implements PlatformRepositoryInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function save(Platform $platform)
    {
        $updatePlatformSql = <<<SQL
    INSERT INTO akeneo_syndication_connected_channel
        (`code`, `label`, `enabled`)
    VALUES
        (:code, :label, :enabled)
    ON DUPLICATE KEY UPDATE
        label = :label,
        enabled = :enabled;
SQL;

        $affectedRows = $this->sqlConnection->executeStatement(
            $updatePlatformSql,
            [
                'code' => $platform->getCode(),
                'label' => $platform->getLabel(),
                'enabled' => true
            ]
        );

        $noChange = $affectedRows === 0;
        $inserted = $affectedRows === 1;
        $updated = $affectedRows === 2;

        if (!($noChange || $inserted || $updated)) {
            throw new \RuntimeException(
                sprintf('Expected to create/update one platform, but %d were affected', $affectedRows)
            );
        }
    }

    public function saveFamily(PlatformFamily $platformFamily): void
    {
        $updateFamilySql = <<<SQL
            INSERT INTO akeneo_syndication_family
                (`code`, `connected_channel_code`, `label`, `requirements`)
            VALUES
                (:code, :connected_channel_code, :label, :requirements)
            ON DUPLICATE KEY UPDATE
                label = :label,
                requirements = :requirements;
        SQL;

        $this->sqlConnection->executeStatement(
            $updateFamilySql,
            [
                'code' => $platformFamily->getCode(),
                'connected_channel_code' => $platformFamily->getPlatformCode(),
                'label' => $platformFamily->getLabel(),
                'requirements' => json_encode($platformFamily->getRequirements())
            ]
        );
    }
}
