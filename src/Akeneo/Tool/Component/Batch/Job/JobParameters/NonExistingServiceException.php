<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

/**
 * This exception should be thrown by service registry when given service does not exists
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistingServiceException extends \DomainException
{
}
