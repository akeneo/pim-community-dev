<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

class AddParentToProduct
{
    /** @var string */
    private $productIdentifier;

    /** @var string */
    private $parentModelCode;

    public function __construct(string $productIdentifier, string $parentModelCode)
    {
        $this->productIdentifier = $productIdentifier;
        $this->parentModelCode = $parentModelCode;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function parentModelCode(): string
    {
        return $this->parentModelCode;
    }
}
