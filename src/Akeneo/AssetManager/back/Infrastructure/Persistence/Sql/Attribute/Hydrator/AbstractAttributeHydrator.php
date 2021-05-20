<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
abstract class AbstractAttributeHydrator implements AttributeHydratorInterface
{
    private AbstractPlatform $platform;

    public function __construct(Connection $sqlConnection)
    {
        $this->platform = $sqlConnection->getDatabasePlatform();
    }

    public function hydrate(array $row): AbstractAttribute
    {
        $this->checkRowProperties($row);
        $row = $this->convertCommonProperties($this->platform, $row);
        $row = $this->convertAdditionalProperties($this->platform, $row);

        return $this->hydrateAttribute($row);
    }

    protected function checkRowProperties(array $row): void
    {
        if (array_key_exists('additional_properties', $row)) {
            $additionalKeys = array_keys(json_decode($row['additional_properties'], true));
            unset($row['additional_properties']);
        }

        $actualKeys = [...array_keys($row), ...$additionalKeys];
        $missingKeys = array_diff($this->getExpectedProperties(), $actualKeys);

        if (!empty($missingKeys)) {
            throw new \RuntimeException(
                sprintf(
                    'Impossible to hydrate the attribute because some information is missing: %s',
                    implode(', ', $missingKeys)
                )
            );
        }
    }

    private function convertCommonProperties(AbstractPlatform $platform, array $row): array
    {
        $row['identifier'] = Type::getType(Type::STRING)->convertToPHPValue($row['identifier'], $platform);
        $row['asset_family_identifier'] = Type::getType(Type::STRING)->convertToPHPValue($row['asset_family_identifier'], $platform);
        $row['code'] = Type::getType(Type::STRING)->convertToPHPValue($row['code'], $platform);
        $row['labels'] = json_decode($row['labels'], true);
        $row['attribute_order'] = Type::getType(Type::INTEGER)->convertToPHPValue($row['attribute_order'], $platform);
        $row['is_required'] = Type::getType(Type::BOOLEAN)->convertToPHPValue($row['is_required'], $platform);
        $row['is_read_only'] = Type::getType(Type::BOOLEAN)->convertToPHPValue($row['is_read_only'], $platform);
        $row['value_per_channel'] = Type::getType(Type::BOOLEAN)->convertToPHPValue($row['value_per_channel'], $platform);
        $row['value_per_locale'] = Type::getType(Type::BOOLEAN)->convertToPHPValue($row['value_per_locale'], $platform);
        $row['additional_properties'] = json_decode($row['additional_properties'], true);

        return $row;
    }

    abstract protected function getExpectedProperties(): array;

    abstract protected function convertAdditionalProperties(AbstractPlatform $platform, array $row): array;

    abstract protected function hydrateAttribute(array $row): AbstractAttribute;
}
