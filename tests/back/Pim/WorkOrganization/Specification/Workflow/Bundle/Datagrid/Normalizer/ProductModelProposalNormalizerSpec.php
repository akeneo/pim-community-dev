<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProposalChangesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProposalNormalizerSpec extends ObjectBehavior
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

    function it_supports_product_proposal_normalization(
        ProductModelDraft $productModelProposal
    ) {
        $this->supportsNormalization($productModelProposal, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization('Foobar', 'datagrid')->shouldReturn(false);

    }

    function it_normalizes_a_product_model_draft(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer,
        UserContext $userContext,
        ProductModelDraft $productModelDraft,
        ProductModelInterface $productModel,
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
        $user->getCatalogLocale()->willReturn('en_US');

        $productModelDraft->getEntityWithValue()->willReturn($productModel);
        $productModelDraft->getId()->willReturn(42);
        $productModelDraft->getCreatedAt()->willReturn($created);
        $productModelDraft->getAuthorLabel()->willReturn('Mary Smith');
        $productModelDraft->getAuthor()->willReturn('mary');
        $productModel->getId()->willReturn(69);
        $productModel->getLabel('en_US')->willReturn('Banana');

        $datagridNormalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');
        $changesNormalizer->normalize($productModelDraft, $context)->willReturn($changes);

        $this->normalize($productModelDraft, 'datagrid', $context)->shouldReturn(
            [
                'proposal_id' => 42,
                'createdAt' => '2017-01-01',
                'author_label' => 'Mary Smith',
                'author_code' => 'mary',
                'document_id' => 69,
                'document_label' => 'Banana',
                'formatted_changes' => $changes,
                'document_type' => 'product_model_draft',
                'id' => 'product_model_draft_42',
            ]
        );
    }
}
