<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\Exception;

use Throwable;

/**
 * Exception thrown when we couldn't get the creation time of the PIM installation
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnavailableCreationTimeException extends \LogicException
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
