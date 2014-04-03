<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

/**
 * Convert MongoDate to DateTime
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeTransformer
{
    /**
     * @param \MongoDate $mongoDate
     *
     * @return \DateTime
     */
    public function transform(\MongoDate $mongoDate)
    {
        $date = new \DateTime();
        $date->setTimestamp($mongoDate->sec);

        return $date;
    }
}
