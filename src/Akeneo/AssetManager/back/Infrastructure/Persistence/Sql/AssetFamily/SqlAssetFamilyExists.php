<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetFamilyExists implements AssetFamilyExistsInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier, bool $caseSensitive = true): bool
    {
        $actualIdentifier = $this->executeQuery($assetFamilyIdentifier);
        if (null === $actualIdentifier) {
            return false;
        }

        return $caseSensitive ? $actualIdentifier->equals($assetFamilyIdentifier) : true;
    }

    private function executeQuery(AssetFamilyIdentifier $assetFamilyIdentifier): ?AssetFamilyIdentifier
    {
        $query = <<<SQL
    SELECT identifier
    FROM akeneo_asset_manager_asset_family
    WHERE identifier = :identifier
SQL;
        $result = $this->sqlConnection->executeQuery(
            $query,
            ['identifier' => (string) $assetFamilyIdentifier]
        )->fetchColumn();

        return is_string($result) ? AssetFamilyIdentifier::fromString($result) : null;
    }
}
