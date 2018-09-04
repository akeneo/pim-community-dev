<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ImageAttributeHydrator extends AbstractAttributeHydrator
{
    public const EXPECTED_KEYS = [
        'identifier',
        'enriched_entity_identifier',
        'code',
        'labels',
        'attribute_order',
        'is_required',
        'value_per_locale',
        'value_per_channel',
        'attribute_type',
        'max_file_size',
        'allowed_extensions'
    ];

    public function supports(array $result): bool
    {
        return isset($result['attribute_type']) && 'image' === $result['attribute_type'];
    }

    public function hydrate(AbstractPlatform $platform, array $result)
    {
        $this->checkResult($result);
        $result = $this->hydrateCommonProperties($platform, $result);
        $extensions = $result['additional_properties']['allowed_extensions'];
        $maxFileSize = $this->getMaxFileSize($platform, $result['additional_properties']['max_file_size']);

        return ImageAttribute::create(
            AttributeIdentifier::fromString($result['identifier']),
            EnrichedEntityIdentifier::fromString($result['enriched_entity_identifier']),
            AttributeCode::fromString($result['code']),
            LabelCollection::fromArray($result['labels']),
            AttributeOrder::fromInteger($result['attribute_order']),
            AttributeIsRequired::fromBoolean($result['is_required']),
            AttributeValuePerChannel::fromBoolean($result['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($result['value_per_locale']),
            $maxFileSize,
            AttributeAllowedExtensions::fromList($extensions)
        );
    }

    private function checkResult($result): void
    {
        $actualKeys = array_keys($result);
        if (isset($result['additional_properties'])) {
            $actualKeys = array_merge(
                $actualKeys,
                array_keys(json_decode($result['additional_properties'], true))
            );
            unset($result['additional_properties']);
        }

        $missingInformation = array_diff(self::EXPECTED_KEYS, $actualKeys);
        $canHydrate = 0 === count($missingInformation);
        if (!$canHydrate) {
            throw new \RuntimeException(
                sprintf(
                    'Impossible to hydrate the image attribute because some information is missing: %s',
                    implode(', ', $missingInformation)
                )
            );
        }
    }

    private function getMaxFileSize(AbstractPlatform $platform, $maxFileSize): AttributeMaxFileSize
    {
        if (null === $maxFileSize) {
            return AttributeMaxFileSize::noLimit();
        }
        $maxFileSize = Type::getType(Type::STRING)->convertToPHPValue($maxFileSize, $platform);

        return AttributeMaxFileSize::fromString($maxFileSize);
    }
}
