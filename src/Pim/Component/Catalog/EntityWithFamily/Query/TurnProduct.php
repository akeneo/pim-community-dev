<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily\Query;

use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * Query function that turns a product into a variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TurnProduct
{
    /**
     * To update product into a variant product in database we need to change product type (data managed by doctrine)
     * and to update doctrine's unit of work, we need to replace the product by the variant product.
     *
     * @param VariantProductInterface $variantProduct
     */
    public function into(VariantProductInterface $variantProduct): void;
}
