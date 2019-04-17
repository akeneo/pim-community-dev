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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

final class ProductId
{
    /** @var int */
    private $productId;

    public function __construct(int $productId)
    {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('Product id should be a positive integer');
        }

        $this->productId = $productId;
    }

    public function toInt(): int
    {
        return $this->productId;
    }

    public function equals(ProductId $productId): bool
    {
        return $this->productId === $productId->toInt();
    }
}
