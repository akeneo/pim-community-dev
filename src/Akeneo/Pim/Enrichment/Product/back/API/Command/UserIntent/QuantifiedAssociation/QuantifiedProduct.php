<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedProduct
{
    public function __construct(private string $productIdentifier, private int $quantity)
    {
        Assert::stringNotEmpty($this->productIdentifier);
        Assert::greaterThan($this->quantity, 0);
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
