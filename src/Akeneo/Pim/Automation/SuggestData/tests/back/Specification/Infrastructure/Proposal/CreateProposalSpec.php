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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\CreateProposalInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal\CreateProposal;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        SaverInterface $draftSaver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($productUpdater, $draftBuilder, $draftSaver, $eventDispatcher);
    }

    function it_is_a_create_proposal()
    {
        $this->shouldHaveType(CreateProposal::class);
        $this->shouldImplement(CreateProposalInterface::class);
    }

    function it_does_not_create_a_proposal_if_suggested_data_is_invalid(
        $productUpdater,
        $draftBuilder,
        EntityWithValuesInterface $product
    ) {
        $suggestedData = [
            'foo' => 'bar',
        ];
        $productUpdater->update($product, ['values' => $suggestedData])->willThrow(
            UnknownPropertyException::unknownProperty('foo')
        );
        $draftBuilder->build($product, 'PIM.ai')->shouldNotBeCalled();

        $this->fromSuggestedData($product, $suggestedData, 'PIM.ai')->shouldReturn(null);
    }

    function it_creates_a_proposal_from_suggested_data(
        $productUpdater,
        $draftBuilder,
        $draftSaver,
        $eventDispatcher,
        EntityWithValuesInterface $product,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $suggestedData = ['foo' => 'bar'];
        $productUpdater->update($product, ['values' => $suggestedData])->willReturn($product);

        $draftBuilder->build($product, 'PIM.ai')->willReturn($productDraft);
        $eventDispatcher->dispatch(
            EntityWithValuesDraftEvents::PRE_READY,
            new GenericEvent($productDraft->getWrappedObject())
        )->shouldBeCalled();

        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $draftSaver->save($productDraft)->shouldBeCalled();

        $this->fromSuggestedData($product, $suggestedData, 'PIM.ai')->shouldReturn(null);
    }
}
