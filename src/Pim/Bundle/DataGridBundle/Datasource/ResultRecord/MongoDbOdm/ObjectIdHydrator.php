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
    public function hydrate($qb, array $options = [])
    {
        $qb->select('_id');

        $results = $qb->getQuery()->execute();

        return array_keys($results->toArray());
    }
}
