<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;

/**
 * Stores a collection of entity identifiers
 *
 * @see Pim\Bundle\CatalogBundle\EventListener\MongoDBODM\EntityReferenceSubscriber
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Entities extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value)
    {
        if (!$value instanceof ReferencedCollection) {
            throw new \InvalidArgumentException('Expecting instance of Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection');
        }

        return $value
            ->map(
                function($val) {
                    return $val->getId();
                }
            )
            ->toArray();
    }
}
