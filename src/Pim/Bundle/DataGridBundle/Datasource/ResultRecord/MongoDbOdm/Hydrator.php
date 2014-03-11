<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Hydrate results of Doctrine MongoDB query as ResultRecord array
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
        $config     = $options['attributes_configuration'];

        $query = $queryBuilder->hydrate(false)->getQuery();
        $results = $query->execute();

        $rows       = [];
        $attributes = [];
        foreach ($config as $attributeConf) {
            $attributes[$attributeConf['id']]= $attributeConf;
        }

        foreach ($results as $result) {
            $result['id']= $result['_id']->__toString();
            unset($result['_id']);
            $result['dataLocale']= $localeCode;
            foreach ($result['values'] as $value) {
                $attribute = $attributes[$value['attribute']];
                $value['attribute']= $attribute;
                $result[$attribute['code']]= $value;
            }
            unset($result['values']);
            // throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
            $result['ratio']= 'temporary';

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }
}
