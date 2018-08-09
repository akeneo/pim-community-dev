<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * Aims to create a product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductQueryBuilderFactoryInterface
{
    /**
     * Create a product query builder
     *
     * @param array $options
     *
     * @return ProductQueryBuilderInterface
     */
    public function create(array $options = []): ProductQueryBuilderInterface;
}
