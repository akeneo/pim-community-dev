<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Hydrate results of Doctrine ODM query as array of ids
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectIdHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($queryBuilder, $options)
    {
        $queryBuilder->hydrate(false);

        $results = $queryBuilder->select('_id')->getQuery()->execute();

        $rows = array();

        foreach ($results as $key => $result) {
            $rows[$key] = $key;
        }

        return $rows;


        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }
}
