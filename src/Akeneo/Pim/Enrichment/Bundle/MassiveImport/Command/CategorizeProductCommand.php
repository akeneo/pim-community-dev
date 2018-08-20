<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command;

class CategorizeProductCommand
{
    /** @var String[] */
    private $categories;

    /**
     * @param String[] $categories
     */
    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }

    public function categories(): array
    {
        return $this->categories;
    }
}
