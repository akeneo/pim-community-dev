<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

class DuplicateProduct
{
    /** @var string */
    private $productId;

    /** @var string */
    private $duplicatedProductIdentifier;

    public function __construct(string $productId, string $duplicatedProductIdentifier)
    {
        $this->productId = $productId;
        $this->duplicatedProductIdentifier = $duplicatedProductIdentifier;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function duplicatedProductIdentifier(): string
    {
        return $this->duplicatedProductIdentifier;
    }
}
