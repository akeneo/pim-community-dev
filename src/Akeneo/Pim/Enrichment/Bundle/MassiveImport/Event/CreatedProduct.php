<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event;

class CreatedProduct
{
    /** @var string */
    private $identifier;

    /**
     * @param string $productIdentifier
     */
    public function __construct(string $productIdentifier)
    {
        $this->identifier = $productIdentifier;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }
}
