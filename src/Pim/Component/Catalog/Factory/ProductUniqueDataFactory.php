<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductUniqueDataInterface;
use Pim\Component\Catalog\Model\ValueInterface;

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
