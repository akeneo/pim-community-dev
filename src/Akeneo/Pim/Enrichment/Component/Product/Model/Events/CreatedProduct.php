<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreatedProduct
{
    /** @var string */
    private $productIdentifier;

    public function __construct(string $productIdentifier)
    {
        $this->productIdentifier = $productIdentifier;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }
}
