<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Webmozart\Assert\Assert;

class ReferenceEntityToStringDataConverter implements ValueDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $sourceAttribute, AttributeInterface $targetAttribute): bool
    {
        return AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT === $sourceAttribute->getType() &&
            in_array(
                $targetAttribute->getType(),
                [
                    AttributeTypes::TEXT,
                    AttributeTypes::TEXTAREA,
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ValueInterface $sourceValue, AttributeInterface $targetAttribute)
    {
        Assert::isInstanceOf($sourceValue->getData(), RecordCode::class);

        return $sourceValue->getData()->__toString();
    }
}
