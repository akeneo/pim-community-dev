<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\ReferenceEntityValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

class ReferenceEntityValueNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityValueNormalizer::class);
        $this->shouldBeAnInstanceOf(AbstractProductValueNormalizer::class);
    }

    function it_supports_reference_entity(ReferenceEntityValue $designerValue)
    {
        $this->supportsNormalization($designerValue, 'flat')->shouldReturn(false);
        $this->supportsNormalization($designerValue, 'indexing_product')->shouldReturn(true);
        $this->supportsNormalization($designerValue, 'indexing_product_and_product_model')->shouldReturn(true);
        $this->supportsNormalization('', 'indexing_product')->shouldReturn(false);
        $this->supportsNormalization(false, 'indexing_product_and_product_model')->shouldReturn(false);
    }

    function it_normalizes_a_null_reference_data_product_value(
        ReferenceEntityValue $designerValue,
        GetAttributes $getAttributes
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $getAttributes->forCode('designer')->willReturn(new Attribute(
            'designer',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            null,
            false,
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION,
            []
        ));

        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $designerValue->getData()->willReturn(null);

        $this->normalize($designerValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'designer-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
            ]
        );
    }

    function it_normalizes_a_reference_data_product_value(
        ReferenceEntityValue $designerValue,
        Record $dyson,
        RecordCode $dysonCode,
        GetAttributes $getAttributes
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $getAttributes->forCode('designer')->willReturn(new Attribute(
            'designer',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            null,
            false,
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION,
            []
        ));

        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $dysonCode->__toString()->willReturn('dyson');
        $dyson->getCode()->willReturn($dysonCode);

        $designerValue->getData()->willReturn($dysonCode);

        $this->normalize($designerValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'designer-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'dyson',
                    ],
                ],
            ]
        );
    }
}
