<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Product builder interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductBuilderInterface extends EntityWithValuesBuilderInterface
{
    /**
     * Create product
     *  - sets the identifier data if provided
     *  - sets family if provided
     *  - sets uuid if provided
     */
    public function createProduct(?string $identifier = null, ?string $familyCode = null, ?string $uuid = null): ProductInterface;
}
