<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\ReferenceEntityCollectionValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Prophecy\Argument;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class ReferenceEntityCollectionValueNormalizerSpec extends ObjectBehavior {

    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
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
        AttributeInterface $designer,
        $attributeRepository
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $designer->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $designer->getCode()->willReturn('designer');

        $attributeRepository->findOneByIdentifier('designer')->willReturn($designer);

        $designerValue->getData()->willReturn([]);

        $this->normalize($designerValue,
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
        AttributeInterface $designer,
        Record $dyson,
        Record $starck,
        RecordCode $dysonCode,
        RecordCode $starckCode,
        $attributeRepository
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $designer->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $designer->getCode()->willReturn('designer');

        $attributeRepository->findOneByIdentifier('designer')->willReturn($designer);

        $dysonCode->__toString()->willReturn('dyson');
        $dyson->getCode()->willReturn($dysonCode);
        $starckCode->__toString()->willReturn('starck');
        $starck->getCode()->willReturn($starckCode);

        $designerValue->getData()->willReturn([$starckCode, $dysonCode]);

        $this->normalize($designerValue,
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
