<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedProductWithUuid
{
    public function __construct(private UuidInterface $productUuid, private int $quantity)
    {
        Assert::greaterThan($this->quantity, 0);
    }

    public function productUuid(): UuidInterface
    {
        return $this->productUuid;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
