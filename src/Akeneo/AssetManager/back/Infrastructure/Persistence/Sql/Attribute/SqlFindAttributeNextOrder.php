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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Doctrine\DBAL\Connection;

class SqlFindAttributeNextOrder implements FindAttributeNextOrderInterface
{
    private Connection $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeOrder
    {
        $query = <<<SQL
        SELECT MAX(attribute_order)
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'asset_family_identifier' => $assetFamilyIdentifier,
        ]);
        $result = $statement->fetchColumn();
        $statement->closeCursor();

        return null === $result ? AttributeOrder::fromInteger(0) : AttributeOrder::fromInteger(((int) $result + 1));
    }
}
