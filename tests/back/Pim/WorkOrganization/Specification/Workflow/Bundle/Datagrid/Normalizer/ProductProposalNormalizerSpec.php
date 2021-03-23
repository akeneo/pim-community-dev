<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProposalChangesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProposalNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer
    ) {
        $this->beConstructedWith($datagridNormalizer, $changesNormalizer);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_product_proposal_normalization(
        ProductDraft $productProposal
    ) {
        $this->supportsNormalization($productProposal, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization('Foobar', 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_product_proposal(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer,
        ProductDraft $productProposal,
        ProductInterface $product
    ) {
        $context = ['locales' => ['en_US']];
        $changes = [
            'text' => [
                ['locale' => 'en_US', 'scope' => null, 'before' => 'beforevalue', 'after' => 'aftervalue'],
                ['locale' => 'fr_FR', 'scope' => null, 'before' => 'avantvalue', 'after' => 'apresvalue'],
            ]
        ];
        $created = new \DateTime('2017-01-01T01:03:34+01:00');

        $productProposal->getEntityWithValue()->willReturn($product);
        $productProposal->getId()->willReturn(42);
        $productProposal->getCreatedAt()->willReturn($created);
        $productProposal->getAuthorLabel()->willReturn('Mary Smith');
        $product->getId()->willReturn(69);
        $product->getLabel()->willReturn('Banana');

        $datagridNormalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');
        $changesNormalizer->normalize($productProposal, $context)->willReturn($changes);

        $this->normalize($productProposal, 'datagrid', $context)->shouldReturn(
            [
                'proposal_id' => 42,
                'createdAt' => '2017-01-01',
                'author_label' => 'Mary Smith',
                'document_id' => 69,
                'document_label' => 'Banana',
                'formatted_changes' => $changes,
                'document_type' => 'product_draft',
            ]
        );
    }
}
