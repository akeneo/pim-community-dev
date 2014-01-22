<?php

namespace Pim\Bundle\FilterBundle\Filter\Flexible;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

/**
 * Flexible filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateRangeFilter extends AbstractFlexibleDateFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return DateRangeFilterType::NAME;
    }
}
