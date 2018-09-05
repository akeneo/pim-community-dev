<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Product;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\AddedValueInProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\CategorizedProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\CreatedProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\EditedValueInProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\UncategorizedProduct;
use Pim\Component\Catalog\Model\ValueCollection;
use Webmozart\Assert\Assert;

class Product
{
    /** @var string */
    private $identifier;

    /** @var bool */
    private $enabled;

    /** @var \Datetime */
    private $createdDate;

    /** @var \DateTime  */
    private $updatedDate;

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
        $this->enabled = true;
        $this->createdDate = new \DateTime();
        $this->updatedDate = new \DateTime();
        $this->categories = $categories;
        $this->valueCollection = $valueCollection;
        $this->events[] = new CreatedProduct($identifier);
    }

    public function fromArray(array $data)
    {
        $this->identifier = $data['identifier'];
        $this->categories = $data['categories'];
        $this->valueCollection = $data['values'];
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function valueCollection(): ValueCollection
    {
        return $this->valueCollection;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function createdDate(): \DateTimeInterface
    {
        return $this->createdDate;
    }

    public function updatedDate(): \DateTimeInterface
    {
        return $this->updatedDate;
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

    public function fillValues(ValueCollection $values)
    {
        // should assert not empty values
        foreach ($values as $value) {
            if (!$this->valueCollection->getSame($value)) {
                $this->valueCollection->add($value);

                /** should be EditedValue for already existing value */
                $events[] = new AddedValueInProduct($this->identifier, $value);

                continue;
            }

            // fix broken isEqual doing reference equality when data is an object (option for example)
            // probably working now due to the identity map pattern used by Doctrine, but leaky as hell of the internal behavior of Doctrine
            $updatedValue = $this->valueCollection->getSame($value);
            if (!$updatedValue->isEqual($value)) {
                $events[] = new EditedValueInProduct($this->identifier, $value);
            }
        }
    }

    public function deleteValues(ValueCollection $values)
    {
        Assert::false('Not implemented. An isEmpty function would be very handy to do it.');
    }

    public function events(): array
    {
        return $this->events;
    }
}
