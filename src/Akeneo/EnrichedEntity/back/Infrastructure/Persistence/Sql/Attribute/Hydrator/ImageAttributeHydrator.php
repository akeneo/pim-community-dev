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

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ImageAttributeHydrator extends AbstractAttributeHydrator
{
    public function supports(array $result): bool
    {
        return isset($result['attribute_type']) && 'image' === $result['attribute_type'];
    }

    public function hydrate(array $result)
    {
        $result = $this->hydrateCommonProperties($result);
        $extensions = $result['additional_properties']['allowed_extensions'];
        $maxFileSize = null === $result['additional_properties']['max_file_size'] ?
            AttributeMaxFileSize::noLimit()
            : AttributeMaxFileSize::fromString($result['additional_properties']['max_file_size']);

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
}
