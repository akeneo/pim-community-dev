<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorizedProduct
{
    /** @var string */
    private $productIdentifier;

    /** @var string */
    private $categoryCode;

    public function __construct(string $productIdentifier, string $categoryCode)
    {
        $this->productIdentifier = $productIdentifier;
        $this->categoryCode = $categoryCode;
    }

    public function categoryCode(): string
    {
        return $this->categoryCode;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }
}
