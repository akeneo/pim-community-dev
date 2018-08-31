<?php

namespace spec\Akeneo\Pim\EnrichedEntity\Component\Normalizer;

use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\Pim\EnrichedEntity\Component\Normalizer\EnrichedEntityCollectionValueNormalizer;
use Akeneo\Pim\EnrichedEntity\Component\Value\EnrichedEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Prophecy\Argument;

class EnrichedEntityCollectionValueNormalizerSpec extends ObjectBehavior {
    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityCollectionValueNormalizer::class);
        $this->shouldBeAnInstanceOf(AbstractProductValueNormalizer::class);
    }

    function it_supports_enriched_entity_collection(EnrichedEntityCollectionValue $designerValue)
    {
        $this->supportsNormalization($designerValue, 'flat')->shouldReturn(false);
        $this->supportsNormalization($designerValue, 'indexing_product')->shouldReturn(true);
        $this->supportsNormalization($designerValue, 'indexing_product_and_product_model')->shouldReturn(true);
        $this->supportsNormalization('', 'indexing_product')->shouldReturn(false);
        $this->supportsNormalization(false, 'indexing_product_and_product_model')->shouldReturn(false);
    }

    function it_normalize_an_empty_reference_data_collection_product_value(
        EnrichedEntityCollectionValue $designerValue,
        AttributeInterface $designer
    ) {
        $designerValue->getAttribute()->willReturn($designer);
        $designer->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $designerValue->getLocale()->willReturn(null);
        $designerValue->getScope()->willReturn(null);

        $designer->getCode()->willReturn('designer');

        $designerValue->getData()->willReturn([]);

        $this->normalize($designerValue,
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
            [
                'designer-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => '',
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_collection_product_value(
        EnrichedEntityCollectionValue $designerValue,
        AttributeInterface $designer,
        Record $dyson,
        Record $starck,
        RecordIdentifier $dysonIdentifier,
        RecordIdentifier $starckIdentifier
    ) {
        $designerValue->getAttribute()->willReturn($designer);
        $designer->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $designerValue->getLocale()->willReturn(null);
        $designerValue->getScope()->willReturn(null);

        $designer->getCode()->willReturn('designer');

        $dysonIdentifier->__toString()->willReturn('dyson');
        $dyson->getIdentifier()->willReturn($dysonIdentifier);
        $starckIdentifier->__toString()->willReturn('starck');
        $starck->getIdentifier()->willReturn($starckIdentifier);

        $designerValue->getData()->willReturn([$starck, $dyson]);

        $this->normalize($designerValue,
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
            [
                'designer-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'starck,dyson',
                    ],
                ],
            ]
        );
    }
}
