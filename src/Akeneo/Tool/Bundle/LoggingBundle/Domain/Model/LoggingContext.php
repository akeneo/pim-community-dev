<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Used to enrich log context this some service parameters, uppon service call.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class LoggingContext
{
}
