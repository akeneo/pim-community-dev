<?php

namespace Pim\Bundle\CatalogBundle\Exception;

/**
 * This exception should be thrown when the user raised a scalability threshold
 * The thresholds are defined for each entity to guarantee an optimal use of Akeneo PIM on a standard infrastructure
 * Each threshold can be increased depending on the project infrastructure configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TooManyEntitiesException extends \RuntimeException
{
}
