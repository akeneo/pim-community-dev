<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Used to Audit a call on the annotated method
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class AuditLog
{

}