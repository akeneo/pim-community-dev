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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UnsubscriptionProcessor implements ItemProcessorInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(ProductSubscriptionRepositoryInterface $productSubscriptionRepository)
    {
        $this->productSubscriptionRepository = $productSubscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $subscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());
        if (null === $subscription) {
            throw new InvalidItemException(
                'Product is not subscribed',
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        return $subscription;
    }
}
