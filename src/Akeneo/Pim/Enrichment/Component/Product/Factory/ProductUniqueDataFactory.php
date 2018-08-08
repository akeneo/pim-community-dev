<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

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

    /**
     * @param string $productUniqueDataClass
     */
    public function __construct($productUniqueDataClass)
    {
        $this->productUniqueDataClass = $productUniqueDataClass;
    }

    /**
     * @param ProductInterface $product
     * @param ValueInterface   $value
     *
     * @return ProductUniqueDataInterface
     */
    public function create(ProductInterface $product, ValueInterface $value)
    {
        return new $this->productUniqueDataClass($product, $value);
    }
}
