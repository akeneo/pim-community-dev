<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\UseCases;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdentifierList // Should be moved elsewhere
{
    private int $totalNumberOfProductIdentifiers;

    /** @var string[] list of product identifiers */
    private array $productIdentifiers;

    /**
     * @param string[] $productIdentifiers
     */
    public function __construct(int $totalNumberOfProductIdentifiers, array $productIdentifiers)
    {
        $this->totalNumberOfProductIdentifiers = $totalNumberOfProductIdentifiers;
        $this->productIdentifiers = $productIdentifiers;
    }

    public function totalNumberOfProductIdentifiers(): int
    {
        return $this->totalNumberOfProductIdentifiers;
    }

    /**
     * @return string[]
     */
    public function productIdentifiers(): array
    {
        return $this->productIdentifiers;
    }
}
