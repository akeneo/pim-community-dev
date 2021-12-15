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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class SqlGetAttributeIdentifier implements GetAttributeIdentifierInterface
{
    public function __construct(private Connection $sqlConnection)
    {
    }

    public function withAssetFamilyAndCode(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        $query = <<<SQL
            SELECT identifier
            FROM akeneo_asset_manager_attribute
            WHERE code = :code AND asset_family_identifier = :asset_family_identifier
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => $attributeCode,
            'asset_family_identifier' => $assetFamilyIdentifier,
        ]);
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        if (!isset($result['identifier'])) {
            throw new \LogicException(
                sprintf(
                    'Attribute identifier not found for "%s" attribute code and "%s" asset family identifier.',
                    $attributeCode,
                    $assetFamilyIdentifier
                )
            );
        }

        $identifier = Type::getType(Types::TEXT)->convertToPhpValue($result['identifier'], $platform);

        return AttributeIdentifier::fromString($identifier);
    }
}
