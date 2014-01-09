<?php

namespace Oro\Bundle\BatchBundle\Job;

/**
 * Exception that stops the job execution
 * Its message will be translated
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RuntimeErrorException extends \RuntimeException
{
    /** @var array */
    protected $messageParameters;

    public function __construct($message, array $messageParameters = array())
    {
        parent::__construct($message);

        $this->messageParameters = $messageParameters;
    }

    public function getMessageParameters()
    {
        return $this->messageParameters;
    }
}
