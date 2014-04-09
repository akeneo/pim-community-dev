<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\Common\Collections\Collection;

/**
 * Stores a collection of entity identifiers
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\CatalogBundle\EventListener\MongoDBODM\EntitiesTypeSubscriber
 */
class Entities extends Type
{
}
