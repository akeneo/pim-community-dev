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
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Product draft interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ProductDraftInterface extends EntityWithValuesInterface, DraftInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param ProductInterface $product
     *
     * @return ProductDraftInterface
     */
    public function setProduct(ProductInterface $product): ProductDraftInterface;

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface;
}
