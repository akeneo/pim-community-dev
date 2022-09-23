<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\UseCases;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelIdentifierList // Should be moved elsewhere
{
    private int $totalNumberOfProductModelIdentifiers;

    /** @var string[] list of product identifiers */
    private array $productModelIdentifiers;

    /**
     * @param string[] $productModelIdentifiers
     */
    public function __construct(int $totalNumberOfProductModelIdentifiers, array $productModelIdentifiers)
    {
        $this->totalNumberOfProductModelIdentifiers = $totalNumberOfProductModelIdentifiers;
        $this->productModelIdentifiers = $productModelIdentifiers;
    }

    public function totalNumberOfProductModelIdentifiers(): int
    {
        return $this->totalNumberOfProductModelIdentifiers;
    }

    /**
     * @return string[]
     */
    public function productModelIdentifiers(): array
    {
        return $this->productModelIdentifiers;
    }
}
