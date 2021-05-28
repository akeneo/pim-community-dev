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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeHasOneValuePerChannelInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlAttributeHasOneValuePerChannel implements AttributeHasOneValuePerChannelInterface
{
    private Connection $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): bool
    {
        $query = <<<SQL
        SELECT value_per_channel
        FROM akeneo_asset_manager_attribute
        WHERE code = :code AND asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => $attributeCode,
            'asset_family_identifier' => $assetFamilyIdentifier,
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if (empty($result)) {
            throw AttributeNotFoundException::withAssetFamilyAndAttributeCode($assetFamilyIdentifier, $attributeCode);
        }

        return Type::getType(Type::BOOLEAN)->convertToPhpValue($result['value_per_channel'], $platform);
    }
}
