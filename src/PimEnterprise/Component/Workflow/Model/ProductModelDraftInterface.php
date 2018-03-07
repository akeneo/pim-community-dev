<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Product model draft interface
 */
interface ProductModelDraftInterface extends EntityWithValuesInterface, DraftInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param ProductModelInterface $productModel
     *
     * @return ProductModelDraftInterface
     */
    public function setProductModel(ProductModelInterface $productModel): ProductModelDraftInterface;

    /**
     * @return ProductModelInterface
     */
    public function getProductModel(): ProductModelInterface;

}
