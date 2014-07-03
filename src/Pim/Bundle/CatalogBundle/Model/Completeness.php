<?php

namespace Pim\Bundle\CatalogBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * Product completeness entity, define the completeness of the enrichment of the product
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class Completeness extends AbstractCompleteness
{
}
