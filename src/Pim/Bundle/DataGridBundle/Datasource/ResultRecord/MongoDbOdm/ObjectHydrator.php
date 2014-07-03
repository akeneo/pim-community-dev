<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Object hydrator for ODM implementation
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $cursor = $qb->getQuery()->execute();

        return $cursor->toArray();
    }
}
