<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\ReferenceEntityValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityValueNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
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
        AttributeInterface $designer,
        $attributeRepository
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $attributeRepository->findOneByIdentifier('designer')->willReturn($designer);
        $designer->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $designer->getCode()->willReturn('designer');

        $designerValue->getData()->willReturn(null);

        $this->normalize($designerValue,
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
        AttributeInterface $designer,
        Record $dyson,
        RecordCode $dysonCode,
        $attributeRepository
    ) {
        $designerValue->getAttributeCode()->willReturn('designer');
        $attributeRepository->findOneByIdentifier('designer')->willReturn($designer);
        $designer->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $designerValue->getLocaleCode()->willReturn(null);
        $designerValue->getScopeCode()->willReturn(null);

        $designer->getCode()->willReturn('designer');

        $dysonCode->__toString()->willReturn('dyson');
        $dyson->getCode()->willReturn($dysonCode);

        $designerValue->getData()->willReturn($dysonCode);

        $this->normalize($designerValue,
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
