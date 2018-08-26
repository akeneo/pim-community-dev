<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event;

class CategorizedProduct
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $categories;

    /**
     * @param string $identifier
     * @param string $categories
     */
    public function __construct($identifier, $categories)
    {
        $this->identifier = $identifier;
        $this->categories = $categories;
    }
}
