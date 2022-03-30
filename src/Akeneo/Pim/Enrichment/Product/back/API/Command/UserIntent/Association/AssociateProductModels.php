<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociateProductModels implements AssociationUserIntent
{
    /**
     * @param array<string> $productModelIdentifiers
     */
    public function __construct(
        private string $associationType,
        private array $productModelIdentifiers,
    ) {
        Assert::notEmpty($productModelIdentifiers);
        Assert::allStringNotEmpty($productModelIdentifiers);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return array<string>
     */
    public function productModelIdentifiers(): array
    {
        return $this->productModelIdentifiers;
    }
}
