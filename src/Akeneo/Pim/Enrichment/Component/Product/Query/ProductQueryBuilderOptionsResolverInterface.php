<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * Resolve product query builder options
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductQueryBuilderOptionsResolverInterface
{
    /**
     * Resolve options for the product query builder
     *
     * @param array $options
     *
     * @return array
     */
    public function resolve(array $options);
}
