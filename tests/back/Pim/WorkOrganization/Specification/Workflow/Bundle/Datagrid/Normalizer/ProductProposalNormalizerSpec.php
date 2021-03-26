<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProposalChangesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityWithValuesProposalNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer,
        UserContext $userContext
    ) {
        $this->beConstructedWith($datagridNormalizer, $changesNormalizer, $userContext);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_entity_with_values_proposal_normalization(
        ProductDraft $productProposal,
        ProductModelDraft $productModelDraft
    ) {
        $this->supportsNormalization($productProposal, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($productModelDraft, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization('Foobar', 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_product_proposal(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer,
        UserContext $userContext,
        LocaleInterface $locale,
        ProductDraft $productProposal,
        ProductInterface $product,
        UserInterface $user
    ) {
        $context = ['locales' => ['en_US']];
        $changes = [
            'text' => [
                ['locale' => 'en_US', 'scope' => null, 'before' => 'beforevalue', 'after' => 'aftervalue'],
                ['locale' => 'fr_FR', 'scope' => null, 'before' => 'avantvalue', 'after' => 'apresvalue'],
            ]
        ];
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $userContext->getUser()->willReturn($user);
        $user->getCatalogLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');

        $productProposal->getEntityWithValue()->willReturn($product);
        $productProposal->getId()->willReturn(42);
        $productProposal->getCreatedAt()->willReturn($created);
        $productProposal->getAuthorLabel()->willReturn('Mary Smith');
        $productProposal->getAuthor()->willReturn('mary');
        $product->getId()->willReturn(69);
        $product->getLabel('en_US')->willReturn('Banana');

        $datagridNormalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');
        $changesNormalizer->normalize($productProposal, $context)->willReturn($changes);

        $this->normalize($productProposal, 'datagrid', $context)->shouldReturn(
            [
                'proposal_id' => 42,
                'createdAt' => '2017-01-01',
                'author_label' => 'Mary Smith',
                'author_code' => 'mary',
                'document_id' => 69,
                'document_label' => 'Banana',
                'formatted_changes' => $changes,
                'document_type' => 'product_draft',
                'id' => 'product_draft_42',
            ]
        );
    }
}
