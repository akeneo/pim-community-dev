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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;

/**
 * DTO used to query the status of a product subscription to Franklin using the product ID.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusQuery
{
    /** @var ProductId */
    private $productId;

    public function __construct(ProductId $productId)
    {
        $this->productId = $productId;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }
}
