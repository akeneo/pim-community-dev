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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsLabelInterface;
use Doctrine\DBAL\Connection;

class SqlFindAssetFamilyAttributeAsLabel implements FindAssetFamilyAttributeAsLabelInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeAsLabelReference
    {
        $query = <<<SQL
        SELECT attribute_as_label
        FROM akeneo_asset_manager_asset_family
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $assetFamilyIdentifier,
        ]);

        $attributeAsLabel = $statement->fetchColumn();
        $statement->closeCursor();

        return false === $attributeAsLabel ?
            AttributeAsLabelReference::noReference() :
            AttributeAsLabelReference::createFromNormalized($attributeAsLabel);
    }
}
