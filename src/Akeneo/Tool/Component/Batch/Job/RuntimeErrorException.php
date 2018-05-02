<?php

namespace Akeneo\Tool\Component\Batch\Job;

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

    /**
     * @param string $message
     * @param array  $messageParameters
     */
    public function __construct($message, array $messageParameters = [])
    {
        parent::__construct($message);

        $this->messageParameters = $messageParameters;
    }

    /**
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }
}
