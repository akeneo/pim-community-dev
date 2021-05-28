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
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\FindMediaFileAttributeCodesInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindMediaFileAttributeCodes implements FindMediaFileAttributeCodesInterface
{
    private Connection $sqlConnection;

    private AbstractPlatform $platform;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
        $this->platform = $sqlConnection->getDatabasePlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $sqlQuery = <<<SQL
            SELECT code
            FROM akeneo_asset_manager_attribute
            WHERE asset_family_identifier = :asset_family_identifier
              AND attribute_type = :attribute_type;
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sqlQuery,
            [
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
                'attribute_type' => MediaFileAttribute::ATTRIBUTE_TYPE
            ]
        );

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            $stringAttributeCode = Type::getType(Type::STRING)->convertToPHPValue(
                $row['code'],
                $this->platform
            );
            return AttributeCode::fromString($stringAttributeCode);
        }, $result ?? []);
    }
}
