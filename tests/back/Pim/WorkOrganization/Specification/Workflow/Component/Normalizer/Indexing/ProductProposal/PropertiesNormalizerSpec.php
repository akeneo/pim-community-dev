<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductProposal;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductProposal\PropertiesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PropertiesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PropertiesNormalizer::class);
    }

    function it_supports_product_proposal(ProductDraft $productProposal)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(false);
        $this->supportsNormalization($productProposal, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productProposal, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(true);
    }

    function it_normalizes_product_proposal(
        $serializer,
        EntityWithValuesDraftInterface $productProposal,
        WriteValueCollection $valueCollection,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productProposal->getId()->willReturn(1);
        $productProposal->getEntityWithValue()->willReturn($product);
        $product->getIdentifier()->willReturn('1');

        $productProposal->getAuthor()->willReturn('mary');
        $product->getCategoryCodes()->willReturn([]);

        $productProposal->getCreatedAt()->willReturn($now);
        $serializer->normalize(
            $productProposal->getWrappedObject()->getCreatedAt(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn(null);
        $serializer->normalize(
            $product->getWrappedObject()->getFamily(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn(['code' => 'family']);

        $productProposal->getValues()->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(true);

        $family->getAttributeAsLabel()->willReturn($attribute);
        $attribute->getCode()->willReturn(null);
        $product->getValue(null)->willReturn(null);

        $this->normalize($productProposal, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(
            [
                'id' => 'product_draft_1',
                'entity_with_values_identifier' => '1',
                'identifier' => '1',
                'created' => $now->format('c'),
                'family' => ['code' => 'family'],
                'author' => 'mary',
                'categories' => [],
                'values' => [],
                'label' => [],
            ]
        );
    }

    function it_normalizes_product_proposal_without_attribute_as_label(
        $serializer,
        EntityWithValuesDraftInterface $productProposal,
        WriteValueCollection $valueCollection,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productProposal->getId()->willReturn(1);
        $productProposal->getEntityWithValue()->willReturn($product);
        $product->getIdentifier()->willReturn('1');

        $productProposal->getAuthor()->willReturn('mary');
        $product->getCategoryCodes()->willReturn([]);

        $productProposal->getCreatedAt()->willReturn($now);
        $serializer->normalize(
            $productProposal->getWrappedObject()->getCreatedAt(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn(null);
        $serializer->normalize(
            $product->getWrappedObject()->getFamily(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn(['code' => 'family']);

        $productProposal->getValues()->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(true);

        $family->getAttributeAsLabel()->willReturn(null);
        $attribute->getCode()->shouldNotBeCalled();
        $product->getValue(null)->willReturn(null);

        $this->normalize($productProposal, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(
            [
                'id' => 'product_draft_1',
                'entity_with_values_identifier' => '1',
                'identifier' => '1',
                'created' => $now->format('c'),
                'family' => ['code' => 'family'],
                'author' => 'mary',
                'categories' => [],
                'values' => [],
                'label' => [],
            ]
        );
    }
}
