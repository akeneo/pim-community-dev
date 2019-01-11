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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

/**
 * This is a DTO holding the id of a product which is being unsubscribed.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class UnsubscribeProductCommand
{
    /** @var int */
    private $productId;

    /**
     * @param int $productId
     */
    public function __construct(int $productId)
    {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('Product id should be a positive integer');
        }
        $this->productId = $productId;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }
}
