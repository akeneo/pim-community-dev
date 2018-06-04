<?php

namespace spec\PimEnterprise\Component\Workflow\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Normalizer\Indexing\ProductProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProposalNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $propertiesNormalizer)
    {
        $this->beConstructedWith($propertiesNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductProposalNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_indexing_normalization_only(ProductDraft $productProposal)
    {
        $this->supportsNormalization($productProposal, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($productProposal, 'other_format')
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')
            ->shouldReturn(false);
    }

    function it_normalizes_the_product_proposal_in_indexing_format(
        $propertiesNormalizer,
        EntityWithValuesDraftInterface $productProposal
    ) {
        $propertiesNormalizer->normalize($productProposal, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX, [])->willReturn(
            ['properties' => 'properties are normalized here']
        );

        $this->normalize($productProposal, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(
            [
                'properties' => 'properties are normalized here',
                'document_type' => ProductDraft::class
            ]
        );
    }
}
