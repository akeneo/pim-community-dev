<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product;

use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Association\Association;
use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Value\ProductValueCollection;

/**
 * DTO representing a product or a product draft to upsert.
 *
 * A property with a null value means that the property is not updated.
 * A value object with a null value means an intent to delete the property data.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product
{
    /** @var ?string */
    private $identifier;

    /** @var null|boolean */
    private $enabled;

    /** @var string */
    private $family;

    /** @var string */
    private $parent;

    /** @var ?array */
    private $categories;

    /** @var ?array */
    private $groups;

    /** @var ProductValueCollection */
    private $values;

    /** @var Association[] */
    private $associations;

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function enabled(): ?bool
    {
        return $this->enabled;
    }

    public function family(): ?string
    {
        return $this->family;
    }

    public function parent(): ?string
    {
        return $this->parent;
    }

    public function categories(): ?array
    {
        return $this->categories;
    }

    public function groups(): ?array
    {
        return $this->groups;
    }

    public function values(): ?ProductValueCollection
    {
        return $this->values;
    }

    public function associations(): ?Association
    {
        return $this->associations;
    }

    public static function fromApiFormat(array $standardFormatProduct): Product
    {
        $product = new self();
        $product->identifier =  $standardFormatProduct['identifier'];

        if (array_key_exists('enabled', $standardFormatProduct)) {
            $product->enabled = $standardFormatProduct['enabled'];
        };

        if (array_key_exists('family', $standardFormatProduct)) {
            $product->family = null === $standardFormatProduct['family'] ?
                '' : $standardFormatProduct['family'];
        };

        if (array_key_exists('parent', $standardFormatProduct)) {
            $product->parent = null === $standardFormatProduct['parent'] ? '' : $standardFormatProduct['parent'];
        };

        if (array_key_exists('categories', $standardFormatProduct)) {
            $product->categories = $standardFormatProduct['categories'];
        };

        if (array_key_exists('groups', $standardFormatProduct)) {
            $product->groups = $standardFormatProduct['groups'];
        };

        if (array_key_exists('values', $standardFormatProduct)) {
            $product->values = ProductValueCollection::fromApiFormat($standardFormatProduct['values']);
        };

        return $product;
    }
}
