<?php

namespace Pim\Bundle\CatalogBundle\MongoDB;

use \MongoId;
use \MongoDate;
use \MongoDBRef;

/**
 * Provides factory method to create common MongoDB objects
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MongoObjectsFactory
{
    /**
     * Create a MongoId
     *
     * @param string $id
     *
     * @return MongoID
     */
    public function createMongoId($id = null)
    {
        return new MongoId($id);
    }

    /**
     * Create a MongoDate
     *
     * @param int $seconds
     *
     * @return MongoDate
     */
    public function createMongoDate($seconds = null)
    {
        if (null === $seconds) {
            $seconds = time();
        }

        return new MongoDate($seconds);
    }

    /**
     * Create a MongoDBRef
     *
     * @param string $id
     * @param string $collection
     *
     * @return MongoDBRef
     */
    public function createMongoDBRef($collection, $id)
    {
        return MongoDBRef::create($collection, $id);
    }
}
