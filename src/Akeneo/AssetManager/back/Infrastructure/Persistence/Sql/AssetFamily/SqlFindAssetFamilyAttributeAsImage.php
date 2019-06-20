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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsImageInterface;
use Doctrine\DBAL\Connection;

class SqlFindAssetFamilyAttributeAsImage implements FindAssetFamilyAttributeAsImageInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeAsImageReference
    {
        $query = <<<SQL
        SELECT attribute_as_image
        FROM akeneo_asset_manager_asset_family
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $assetFamilyIdentifier,
        ]);

        $attributeAsImage = $statement->fetchColumn();
        $statement->closeCursor();

        return false === $attributeAsImage ?
            AttributeAsImageReference::noReference() :
            AttributeAsImageReference::createFromNormalized($attributeAsImage);
    }
}
