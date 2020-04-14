<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\ReferenceEntityCollectionValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionValueNormalizerSpec extends ObjectBehavior {

    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionValueNormalizer::class);
        $this->shouldBeAnInstanceOf(AbstractProductValueNormalizer::class);
    }

    function it_supports_reference_entity_collection(ReferenceEntityCollectionValue $designerValue)
    {
        $this->supportsNormalization($designerValue, 'flat')->shouldReturn(false);
        $this->supportsNormalization($designerValue, 'indexing_product')->shouldReturn(true);
        $this->supportsNormalization($designerValue, 'indexing_product_and_product_model')->shouldReturn(true);
        $this->supportsNormalization('', 'indexing_product')->shouldReturn(false);
        $this->supportsNormalization(false, 'indexing_product_and_product_model')->shouldReturn(false);
    }

    function it_normalize_an_empty_reference_data_collection_product_value(
        ReferenceEntityCollectionValue $designerValue,
        GetAttributes $getAttributes
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $getAttributes->forCode('designer')->willReturn(new Attribute(
            'designer',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            null,
            false,
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS,
            []
        ));

        $designerValue->getData()->willReturn([]);

        $this->normalize($designerValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'designer-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [],
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_collection_product_value(
        ReferenceEntityCollectionValue $designerValue,
        Record $dyson,
        Record $starck,
        RecordCode $dysonCode,
        RecordCode $starckCode,
        GetAttributes $getAttributes
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $getAttributes->forCode('designer')->willReturn(new Attribute(
            'designer',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            null,
            false,
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS,
            []
        ));

        $dysonCode->__toString()->willReturn('dyson');
        $dyson->getCode()->willReturn($dysonCode);
        $starckCode->__toString()->willReturn('starck');
        $starck->getCode()->willReturn($starckCode);

        $designerValue->getData()->willReturn([$starckCode, $dysonCode]);

        $this->normalize($designerValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'designer-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => ['starck', 'dyson'],
                    ],
                ],
            ]
        );
    }
}
