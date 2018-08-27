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

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;

/**
 * Handles a GetProductSubscriptionStatus query and returns a ProductSubscriptionStatus read model.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusHandler
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
     * @param GetProductSubscriptionStatus $getProductSubscription
     *
     * @return ProductSubscriptionStatus
     */
    public function handle(GetProductSubscriptionStatus $getProductSubscription): ProductSubscriptionStatus
    {
        $productSubscription = $this->productSubscriptionRepository->findOneByProductId(
            $getProductSubscription->getProductId()
        );

        return new ProductSubscriptionStatus($productSubscription instanceof ProductSubscription);
    }
}
