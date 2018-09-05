<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\HydratorInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractAttributeHydrator implements HydratorInterface
{
    public function hydrate(AbstractPlatform $platform, array $result): AbstractAttribute
    {
        $this->checkResultKeys($result);
        $result = $this->convertCommonProperties($platform, $result);
        $result = $this->convertAdditionalProperties($platform, $result);

        return $this->hydrateAttribute($result);
    }

    protected function checkResultKeys(array $result): void
    {
        $additionalKeys = array_keys($result);
        if (in_array('additional_properties', $additionalKeys)) {
            $additionalKeys = array_keys(json_decode($result['additional_properties'], true));
            unset($result['additional_properties']);
        }

        $actualKeys = array_merge(array_keys($result), $additionalKeys);
        $missingKeys = array_diff($this->getExpectedKeys(), $actualKeys);

        if (!empty($missingKeys)) {
            throw new \RuntimeException(
                sprintf(
                    'Impossible to hydrate the attribute because some information is missing: %s',
                    implode(', ', $missingKeys)
                )
            );
        }
    }

    private function convertCommonProperties(AbstractPlatform $platform, array $result): array
    {
        $result['identifier'] = Type::getType(Type::STRING)->convertToPHPValue($result['identifier'], $platform);
        $result['enriched_entity_identifier'] = Type::getType(Type::STRING)->convertToPHPValue($result['enriched_entity_identifier'], $platform);
        $result['code'] = Type::getType(Type::STRING)->convertToPHPValue($result['code'], $platform);
        $result['labels'] = json_decode($result['labels'], true);
        $result['attribute_order'] = Type::getType(Type::INTEGER)->convertToPHPValue($result['attribute_order'], $platform);
        $result['is_required'] = Type::getType(Type::BOOLEAN)->convertToPHPValue($result['is_required'], $platform);
        $result['value_per_channel'] = Type::getType(Type::BOOLEAN)->convertToPHPValue($result['value_per_channel'], $platform);
        $result['value_per_locale'] = Type::getType(Type::BOOLEAN)->convertToPHPValue($result['value_per_locale'], $platform);
        $result['additional_properties'] = json_decode($result['additional_properties'], true);

        return $result;
    }

    abstract protected function getExpectedKeys(): array;

    abstract protected function convertAdditionalProperties(AbstractPlatform $platform, array $result): array;

    abstract protected function hydrateAttribute(array $result): AbstractAttribute;
}
