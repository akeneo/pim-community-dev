<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;

/**
 * Stores an entity identifier
 *
 * @see Pim\Bundle\CatalogBundle\EventListener\MongoDBODM\EntityTypeSubscriber
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Entity extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value)
    {
        return $value->getId();
    }
}
