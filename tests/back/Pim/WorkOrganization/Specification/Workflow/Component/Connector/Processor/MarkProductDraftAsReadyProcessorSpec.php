<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor\MarkProductDraftAsReadyProcessor;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

class MarkProductDraftAsReadyProcessorSpec extends ObjectBehavior
{
    public function it_is_a_product_draft_as_ready_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldHaveType(MarkProductDraftAsReadyProcessor::class);
    }

    public function it_marks_a_product_draft_as_ready(EntityWithValuesDraftInterface $productDraft)
    {
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $productDraft->markAsReady()->shouldBeCalled();

        $this->process($productDraft);
    }
}
