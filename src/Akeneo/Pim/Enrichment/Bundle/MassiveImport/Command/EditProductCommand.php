<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\ValueCollection;

class EditProductCommand
{
    /** @var string */
    private $identifier;

    /** @var String[] */
    private $categories;

    /** ValueCollection */
    private $valueCollection;

    /**
     * @param string          $identifier
     * @param String[]        $categories
     * @param ValueCollection $valueCollection
     */
    public function __construct(string $identifier, ?array $categories, ?ValueCollection $valueCollection)
    {
        $this->identifier = $identifier;
        $this->categories = $categories;
        $this->valueCollection = $valueCollection;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function categories(): ?array
    {
        return $this->categories;
    }

    public function values(): ?ValueCollection
    {
        return$this->valueCollection;
    }
}
