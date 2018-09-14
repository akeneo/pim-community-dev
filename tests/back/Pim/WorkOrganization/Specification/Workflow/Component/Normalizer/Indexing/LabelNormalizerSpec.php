<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\LabelNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductProposalNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\TextNormalizer;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LabelNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LabelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_text_product_value(
        ValueInterface $numberValue,
        ValueInterface $textValue,
        AttributeInterface $numberAttribute,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('text_attribute');
        $numberValue->getAttributeCode()->willReturn('number_attribute');

        $attributeRepository->findOneByIdentifier('text_attribute')->willReturn($textAttribute);
        $attributeRepository->findOneByIdentifier('number_attribute')->willReturn($numberAttribute);

        $textAttribute->getBackendType()->willReturn('text');
        $numberAttribute->getBackendType()->willReturn('decimal');

        $this->supportsNormalization(new \stdClass(), ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(true);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($numberValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_text_product_value_with_no_locale_and_no_channel(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_an_empty_text_with_no_locale_and_channel(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn(null);

        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => null,
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_no_scope(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn('fr_FR');
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_scope_and_no_locale(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_scope(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn('fr_FR');
        $textValue->getScopeCode()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }
}
