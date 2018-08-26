<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Product;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\AddedValue;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\CategorizedProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\UncategorizedProduct;
use Pim\Component\Catalog\Model\ValueCollection;

class Product
{
    /** @var string */
    private $identifier;

    /** @var array */
    private $categories;

    /** @var ValueCollection */
    private $valueCollection;

    /** @var array */
    private $events = [];

    /**
     * @param $identifier
     * @param $categories
     * @param $valueCollection
     */
    public function __construct(string $identifier, array $categories, ValueCollection $valueCollection)
    {
        $this->identifier = $identifier;
        $this->categories = $categories;
        $this->valueCollection = $valueCollection;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function valueCollection(): array
    {
        return $this->valueCollection;
    }

    public function categorize(array $categories) : void
    {
        $categorized = array_diff($categories, $this->categories);
        $uncategorized = array_diff($this->categories, $categories);

        if (!empty($categorized)) {
            $events[] = new CategorizedProduct($this->identifier, $categorized);
        }

        if (!empty($uncategorized)) {
            $events[] = new UncategorizedProduct($this->identifier, $uncategorized);
        }

        $this->categories = $categories;
    }

    public function addValues(ValueCollection $values)
    {
        foreach ($values as $value) {
            $this->valueCollection->add($value);

            $events[] = new AddedValue($this->identifier, $value);
        }


    }
}
