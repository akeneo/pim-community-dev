<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Hydrate results of Doctrine ORM query as ResultRecord array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Hydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($queryBuilder, $options)
    {
        $localeCode = $options['locale_code'];

        $query = $queryBuilder->getQuery();
        $results = $query->getArrayResult();

        $rows    = [];
        foreach ($results as $result) {
            $entityFields = $result[0];
            unset($result[0]);
            $otherFields = $result;
            $result = $entityFields + $otherFields;
            if (isset($result['values'])) {
                $values = $result['values'];
                foreach ($values as $value) {
                    $result[$value['attribute']['code']]= $value;
                }
                unset($result['values']);
            }
            $result['dataLocale']= $localeCode;

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }
}
