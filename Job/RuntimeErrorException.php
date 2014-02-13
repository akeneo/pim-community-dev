<?php

namespace Akeneo\Bundle\BatchBundle\Job;

/**
 * Exception that stops the job execution
 * Its message will be translated
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
