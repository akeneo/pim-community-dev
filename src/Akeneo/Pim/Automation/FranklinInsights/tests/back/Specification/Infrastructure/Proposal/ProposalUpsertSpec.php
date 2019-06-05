<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\ProposalUpsert;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalUpsertSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        SaverInterface $draftSaver,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith(
            $productRepository,
            $productUpdater,
            $draftBuilder,
            $draftSaver,
            $eventDispatcher
        );
    }

    public function it_is_a_proposal_upsert(): void
    {
        $this->shouldHaveType(ProposalUpsert::class);
        $this->shouldImplement(ProposalUpsertInterface::class);
    }

    public function it_creates_proposals_from_suggested_data(
        $productRepository,
        $productUpdater,
        $draftBuilder,
        $draftSaver,
        $eventDispatcher,
        ProductInterface $product,
        FamilyInterface $family,
        ProductInterface $otherProduct,
        FamilyInterface $otherFamily,
        EntityWithValuesDraftInterface $productDraft,
        EntityWithValuesDraftInterface $otherProductDraft
    ): void {
        $family->getAttributeCodes()->willReturn(['foo']);
        $product->getFamily()->willReturn($family);
        $productRepository->find(42)->willReturn($product);
        $suggestedData = ['foo' => 'bar'];
        $draftBuilder->build($product, 'Franklin')->willReturn($productDraft);

        $otherFamily->getAttributeCodes()->willReturn(['test']);
        $otherProduct->getFamily()->willReturn($otherFamily);
        $productRepository->find(56)->willReturn($otherProduct);
        $otherSuggestedData = ['test' => 42];
        $draftBuilder->build($otherProduct, 'Franklin')->willReturn($otherProductDraft);

        $productUpdater->update($product, ['values' => $suggestedData])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $productDraft->markAsReady()->shouldBeCalled();
        $draftSaver->save($productDraft)->shouldBeCalled();

        $productUpdater->update($otherProduct, ['values' => $otherSuggestedData])->shouldBeCalled();
        $otherProductDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $otherProductDraft->markAsReady()->shouldBeCalled();
        $draftSaver->save($otherProductDraft)->shouldBeCalled();

        $eventDispatcher->dispatch(
            EntityWithValuesDraftEvents::PRE_READY,
            Argument::type(GenericEvent::class)
        )->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(
            EntityWithValuesDraftEvents::POST_READY,
            Argument::type(GenericEvent::class)
        )->shouldBeCalledTimes(2);

        $this->process(
            [
                new ProposalSuggestedData(new ProductId(42), $suggestedData),
                new ProposalSuggestedData(new ProductId(56), $otherSuggestedData),
            ],
            'Franklin'
        )->shouldReturn(2);
    }

    public function it_skips_the_proposal_creation_if_there_is_an_error(
        $productRepository,
        $productUpdater,
        $draftBuilder,
        ProductInterface $product,
        FamilyInterface $family,
        ProductInterface $otherProduct,
        FamilyInterface $otherFamily
    ): void {
        $family->getAttributeCodes()->willReturn(['foo']);
        $product->getFamily()->willReturn($family);
        $productRepository->find(42)->willReturn($product);
        $suggestedData = ['foo' => 'bar'];
        $productUpdater->update($product, ['values' => $suggestedData])->willThrow(new \LogicException());
        $draftBuilder->build($product, 'Franklin')->shouldNotBeCalled();

        $otherFamily->getAttributeCodes()->willReturn(['test']);
        $otherProduct->getFamily()->willReturn($otherFamily);
        $productRepository->find(56)->willReturn($otherProduct);
        $otherSuggestedData = ['test' => 42];
        $productUpdater->update($otherProduct, ['values' => $otherSuggestedData])->willReturn($otherProduct);
        $draftBuilder->build($otherProduct, 'Franklin')->willThrow(new \LogicException());

        $this->process(
            [
                new ProposalSuggestedData(new ProductId(42), $suggestedData),
                new ProposalSuggestedData(new ProductId(56), $otherSuggestedData),
            ],
            'Franklin'
        )->shouldReturn(0);
    }

    public function it_filters_attributes_that_do_not_belong_to_the_products_family(
        $productRepository,
        $productUpdater,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $family->getAttributeCodes()->willReturn(['color', 'weight', 'size']);
        $product->getFamily()->willReturn($family);
        $productRepository->find(42)->willReturn($product);
        $suggestedData = ['color' => 'black', 'height' => '35', 'name' => 'Lorem ipsum'];
        $productUpdater->update($product, ['values' => ['color' => 'black']])->shouldBeCalled();

        $this->process(
            [
                new ProposalSuggestedData(new ProductId(42), $suggestedData),
            ],
            'Franklin'
        )->shouldReturn(0);
    }
}
