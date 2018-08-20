<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\ValueCollection;

class FillProductValuesCommand
{
    /** @var string */
    private $productIdentifier;

    /** ValueCollection */
    private $valueCollection;

    public function __construct(string $productIdentifier, ValueCollection $valueCollection)
    {
        $this->productIdentifier = $productIdentifier;
        $this->valueCollection = $valueCollection;
    }

    public function values(): ValueCollection
    {
        return $this->valueCollection;
    }
}
