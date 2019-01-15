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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Factory\ProposalSuggestedDataFactory;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PendingSubscriptionProcessor implements ItemProcessorInterface
{
    /** @var ProposalSuggestedDataFactory */
    private $suggestedDataFactory;

    /**
     * @param ProposalSuggestedDataFactory $suggestedDataFactory
     */
    public function __construct(ProposalSuggestedDataFactory $suggestedDataFactory)
    {
        $this->suggestedDataFactory = $suggestedDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process($subscription)
    {
        $suggestedData = $this->suggestedDataFactory->fromSubscription($subscription);
        if (null === $suggestedData) {
            throw new InvalidItemException(
                'No suggested data for the following product',
                new DataInvalidItem(['product_id' => $subscription->getProductId()])
            );
        }

        return $suggestedData;
    }
}
