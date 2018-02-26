<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Filter\Field;

use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\IdentifierFilter as BaseIdentifierFilter;

/**
 * Override default Elasticsearch identifier filter for proposal
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class IdentifierFilter extends BaseIdentifierFilter
{
    const IDENTIFIER_KEY = 'product_identifier';
}
