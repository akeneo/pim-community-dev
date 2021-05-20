<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsMainMediaInterface;
use Doctrine\DBAL\Connection;

class SqlFindAssetFamilyAttributeAsMainMedia implements FindAssetFamilyAttributeAsMainMediaInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeAsMainMediaReference
    {
        $query = <<<SQL
        SELECT attribute_as_main_media
        FROM akeneo_asset_manager_asset_family
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $assetFamilyIdentifier,
        ]);

        $attributeAsMainMedia = $statement->fetchColumn();
        $statement->closeCursor();

        return false === $attributeAsMainMedia ?
            AttributeAsMainMediaReference::noReference() :
            AttributeAsMainMediaReference::createFromNormalized($attributeAsMainMedia);
    }
}
