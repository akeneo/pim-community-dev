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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class MarkProductDraftAsReadyProcessor implements ItemProcessorInterface
{
    /**
     * @param EntityWithValuesDraftInterface
     *
     * @return EntityWithValuesDraftInterface
     */
    public function process($productDraft)
    {
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $productDraft->markAsReady();

        return $productDraft;
    }
}
