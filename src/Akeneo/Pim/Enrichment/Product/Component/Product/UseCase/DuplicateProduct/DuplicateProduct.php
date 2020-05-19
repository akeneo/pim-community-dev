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

final class DuplicateProduct
{
    /** @var string */
    private $productToDuplicateIdentifier;

    /** @var string */
    private $duplicatedProductIdentifier;

    /** @var int */
    private $userId;

    public function __construct(
        string $productToDuplicateIdentifier,
        string $duplicatedProductIdentifier,
        int $userId
    ) {
        $this->productToDuplicateIdentifier = $productToDuplicateIdentifier;
        $this->duplicatedProductIdentifier = $duplicatedProductIdentifier;
        $this->userId = $userId;
    }

    public function productToDuplicateIdentifier(): string
    {
        return $this->productToDuplicateIdentifier;
    }

    public function duplicatedProductIdentifier(): string
    {
        return $this->duplicatedProductIdentifier;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}
