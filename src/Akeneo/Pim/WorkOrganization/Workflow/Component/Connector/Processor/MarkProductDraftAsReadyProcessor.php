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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class MarkProductDraftAsReadyProcessor implements ItemProcessorInterface
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return EntityWithValuesDraftInterface
     */
    public function process($productDraft)
    {
        $this->eventDispatcher->dispatch(new GenericEvent($productDraft), EntityWithValuesDraftEvents::PRE_READY);

        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $productDraft->markAsReady();

        return $productDraft;
    }
}
