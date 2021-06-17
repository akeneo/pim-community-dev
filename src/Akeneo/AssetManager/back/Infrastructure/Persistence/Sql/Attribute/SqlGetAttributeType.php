<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlGetAttributeType implements GetAttributeTypeInterface
{
    private Connection $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): string
    {
        $query = <<<SQL
        SELECT attribute_type
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier AND code = :attribute_code;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
                'attribute_code' => (string) $attributeCode
            ]
        );
        $result = $statement->fetch(PDO::FETCH_COLUMN);

        if (false === $result) {
            throw AttributeNotFoundException::withAssetFamilyAndAttributeCode($assetFamilyIdentifier, $attributeCode);
        }

        return $result;
    }
}
