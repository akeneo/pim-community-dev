<?php

namespace Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * Stores a collection of entity identifiers
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber
 */
class Entities extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value)
    {
        if (!$value instanceof Collection) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expecting instance of Doctrine\Common\Collections\Collection, got "%s"',
                    'object' === gettype($value) ? get_class($value) : gettype($value)
                )
            );
        }

        return $value
            ->map(
                function ($val) {
                    return $val->getId();
                }
            )
            ->toArray();
    }
}
