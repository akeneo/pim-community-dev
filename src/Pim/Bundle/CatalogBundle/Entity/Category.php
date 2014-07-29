<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pim\Bundle\CatalogBundle\Model\AbstractCategory;

/**
 * Category. Business code is in AbstractCategory.
 * This class can be overriden
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Config(
 *     defaultValues={
 *         "entity"={"label"="Category", "plural_label"="Categories"}
 *     }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Category extends AbstractCategory
{
}
