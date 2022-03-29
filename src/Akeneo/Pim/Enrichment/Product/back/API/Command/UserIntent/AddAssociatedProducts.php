<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// SetAssociatedProducts (permettre de set avec un tableau vide)
// AddAssociatedProducts
// RemoveAssociatedProducts
final class AddAssociatedProducts implements AssociationsUserIntent
{
    /**
     * @param array<string> $productIdentifiers
     */
    public function __construct(
        private string $associationType,
        private array $productIdentifiers = [],
    ) {
        Assert::notEmpty($productIdentifiers);
        Assert::allStringNotEmpty($productIdentifiers);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return array<string>
     */
    public function productIdentifiers(): array
    {
        return $this->productIdentifiers;
    }
}
