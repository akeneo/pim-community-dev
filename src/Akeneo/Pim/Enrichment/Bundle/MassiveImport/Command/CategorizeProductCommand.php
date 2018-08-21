<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command;

class CategorizeProductCommand
{
    /** @var string */
    private $identifier;

    /** @var String[] */
    private $categories;

    /**
     * @param string   $identifier
     * @param String[] $categories
     */
    public function __construct(string $identifier, array $categories)
    {
        $this->identifier = $identifier;
        $this->categories = $categories;
    }

    public function categories(): array
    {
        return $this->categories;
    }
}
