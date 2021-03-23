<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProposalChangesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProposalNormalizerSpec extends ObjectBehavior
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
        ProductModelDraft $productModelProposal
    ) {
        $this->supportsNormalization($productModelProposal, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization('Foobar', 'datagrid')->shouldReturn(false);

    }

    function it_normalizes_a_product_model_draft(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer,
        ProductModelDraft $productModelDraft,
        ProductModelInterface $productModel
    ) {
        $context = ['locales' => ['en_US']];
        $changes = [
            'text' => [
                ['locale' => 'en_US', 'scope' => null, 'before' => 'beforevalue', 'after' => 'aftervalue'],
                ['locale' => 'fr_FR', 'scope' => null, 'before' => 'avantvalue', 'after' => 'apresvalue'],
            ]
        ];
        $created = new \DateTime('2017-01-01T01:03:34+01:00');

        $productModelDraft->getEntityWithValue()->willReturn($productModel);
        $productModelDraft->getId()->willReturn(42);
        $productModelDraft->getCreatedAt()->willReturn($created);
        $productModelDraft->getAuthorLabel()->willReturn('Mary Smith');
        $productModel->getId()->willReturn(69);
        $productModel->getLabel()->willReturn('Banana');

        $datagridNormalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');
        $changesNormalizer->normalize($productModelDraft, $context)->willReturn($changes);

        $this->normalize($productModelDraft, 'datagrid', $context)->shouldReturn(
            [
                'proposal_id' => 42,
                'createdAt' => '2017-01-01',
                'author_label' => 'Mary Smith',
                'document_id' => 69,
                'document_label' => 'Banana',
                'formatted_changes' => $changes,
                'document_type' => 'product_model_draft',
            ]
        );
    }
}
