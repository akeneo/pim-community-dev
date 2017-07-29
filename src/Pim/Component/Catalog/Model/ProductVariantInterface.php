<?php

namespace Pim\Component\Catalog\Model;

/**
 * Product variant. An entity that belongs to a family variant and that contains flexible values,
 * completeness, categories, associations and much more...
 *
 * @author    Julien Janvier <j.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductVariantInterface extends ProductInterface
{
    /**
     * @return ProductModelInterface|null
     */
    public function getProductModel(): ?ProductModelInterface;

    /**
     * @param ProductModelInterface $productModel
     *
     * @return ProductInterface
     */
    public function setProductModel(ProductModelInterface $productModel): ProductInterface;
}
