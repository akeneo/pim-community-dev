<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event;

class UncategorizedProduct
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $category;

    /**
     * @param string $identifier
     * @param string $category
     */
    public function __construct($identifier, $category)
    {
        $this->identifier = $identifier;
        $this->category = $category;
    }
}
