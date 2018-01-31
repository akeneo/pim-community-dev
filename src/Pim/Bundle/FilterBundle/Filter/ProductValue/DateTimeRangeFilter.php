<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

/**
 * Date time filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeRangeFilter extends AbstractDateFilter
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * {@inheritdoc}
     *
     * Override to set the time of the DateTime object on-the-fly according to the chosen operator.
     */
    public function parseData($data)
    {
        switch ($data['type']) {
            case DateRangeFilterType::TYPE_MORE_THAN:
                $data['value']['start']->setTime(23, 59, 59);
                break;
            case DateRangeFilterType::TYPE_LESS_THAN:
                $data['value']['end']->setTime(0, 0, 0);
                break;
            default:
                if (isset($data['value']['start']) && $data['value']['start'] instanceof \DateTime) {
                    $data['value']['start']->setTime(0, 0, 0);
                }
                if (isset($data['value']['end']) && $data['value']['end'] instanceof \DateTime) {
                    $data['value']['end']->setTime(23, 59, 59);
                }
        }

        return parent::parseData($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return DateRangeFilterType::class;
    }
}
