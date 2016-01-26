<?php

namespace Pim\Component\Catalog\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\EmptyChecker\ProductValue\EmptyCheckerInterface;

/**
 * Remove empty product values from a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPurger implements ProductPurgerInterface
{
    /** @var EmptyCheckerInterface */
    protected $checker;

    /**
     * @param EmptyCheckerInterface $checker
     */
    public function __construct(EmptyCheckerInterface $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool has removed at least one value
     */
    public function removeEmptyProductValues(ProductInterface $product)
    {
        $purgedProduct = false;
        foreach ($product->getValues() as $value) {
            $removedValue = $this->removeEmptyProductValue($product, $value);
            if (true === $removedValue) {
                $purgedProduct = true;
            }
        }

        return $purgedProduct;
    }

    /**
     * @param ProductInterface      $product
     * @param ProductValueInterface $productValue
     *
     * @return bool product value has been removed
     */
    protected function removeEmptyProductValue(ProductInterface $product, ProductValueInterface $productValue)
    {
        if ($this->checker->isEmpty($productValue)) {
            $product->removeValue($productValue);
            return true;
        } else {
            return false;
        }
    }
}
