<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Creates and configures a product unique data.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUniqueDataFactory
{
    /** @var string */
    protected $productUniqueDataClass;

    public function __construct(string $productUniqueDataClass)
    {
        $this->productUniqueDataClass = $productUniqueDataClass;
    }

    public function create(ProductInterface $product, AttributeInterface $attribute, string $rawData): ProductUniqueDataInterface
    {
        return new $this->productUniqueDataClass($product, $attribute, $rawData);
    }
}
