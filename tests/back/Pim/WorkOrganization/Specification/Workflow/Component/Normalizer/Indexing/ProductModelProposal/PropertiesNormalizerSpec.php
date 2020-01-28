<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductModelProposal;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductModelProposal\PropertiesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductModelProposalNormalizer;
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

    function it_supports_product_model_proposal(ProductModelDraft $productModel)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(false);
        $this->supportsNormalization($productModel, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productModel, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)->shouldReturn(true);
    }

    function it_normalizes_product_model_proposal(
        $serializer,
        EntityWithValuesDraftInterface $productModelProposal,
        WriteValueCollection $valueCollection,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModelProposal->getId()->willReturn(1);
        $productModelProposal->getEntityWithValue()->willReturn($productModel);
        $productModel->getCode()->willReturn('code');

        $productModelProposal->getAuthor()->willReturn('mary');
        $productModelProposal->getSource()->willReturn('pim');
        $productModel->getCategoryCodes()->willReturn([]);

        $productModelProposal->getCreatedAt()->willReturn($now);
        $serializer->normalize(
            $productModelProposal->getWrappedObject()->getCreatedAt(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $productModel->getFamily()->willReturn($family);
        $family->getCode()->willReturn(null);
        $serializer->normalize(
            $productModel->getWrappedObject()->getFamily(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn(['code' => 'family']);

        $productModelProposal->getValues()->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(true);

        $family->getAttributeAsLabel()->willReturn($attribute);
        $attribute->getCode()->willReturn(null);
        $productModel->getValue(null)->willReturn(null);

        $this->normalize($productModelProposal, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)->shouldReturn(
            [
                'id' => 'product_model_draft_1',
                'entity_with_values_identifier' => 'code',
                'identifier' => '1',
                'created' => $now->format('c'),
                'family' => ['code' => 'family'],
                'author' => 'mary',
                'source' => 'pim',
                'categories' => [],
                'values' => [],
                'label' => [],
            ]
        );
    }

    function it_normalizes_product_model_proposal_without_attribute_as_label(
        $serializer,
        EntityWithValuesDraftInterface $productModelProposal,
        WriteValueCollection $valueCollection,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModelProposal->getId()->willReturn(1);
        $productModelProposal->getEntityWithValue()->willReturn($productModel);
        $productModel->getCode()->willReturn('code');

        $productModelProposal->getAuthor()->willReturn('mary');
        $productModelProposal->getSource()->willReturn('pim');
        $productModel->getCategoryCodes()->willReturn([]);

        $productModelProposal->getCreatedAt()->willReturn($now);
        $serializer->normalize(
            $productModelProposal->getWrappedObject()->getCreatedAt(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $productModel->getFamily()->willReturn($family);
        $family->getCode()->willReturn(null);
        $serializer->normalize(
            $productModel->getWrappedObject()->getFamily(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn(['code' => 'family']);

        $productModelProposal->getValues()->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(true);

        $family->getAttributeAsLabel()->willReturn(null);
        $attribute->getCode()->shouldNotBeCalled();
        $productModel->getValue(null)->willReturn(null);

        $this->normalize($productModelProposal, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)->shouldReturn(
            [
                'id' => 'product_model_draft_1',
                'entity_with_values_identifier' => 'code',
                'identifier' => '1',
                'created' => $now->format('c'),
                'family' => ['code' => 'family'],
                'author' => 'mary',
                'source' => 'pim',
                'categories' => [],
                'values' => [],
                'label' => [],
            ]
        );
    }
}
