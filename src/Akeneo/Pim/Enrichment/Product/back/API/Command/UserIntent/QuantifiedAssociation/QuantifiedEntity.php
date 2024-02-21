<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedEntity
{
    public function __construct(private readonly string $entityIdentifier, private readonly int $quantity)
    {
        Assert::stringNotEmpty($this->entityIdentifier);
    }

    public function entityIdentifier(): string
    {
        return $this->entityIdentifier;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
