<?php


namespace AkeneoTest\Tool\Integration\Logging\src;

use Psr\Log\AbstractLogger;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryLogger extends AbstractLogger
{
    private $loggedMessages = [];

    public function log($level, $message, array $context = array())
    {
        if (!isset($this->loggedMessages)) {
            $this->loggedMessages = [];
        }
        $this->loggedMessages[] = ['log_level' => $level, 'message' => $message, 'context' => $context];
    }

    /**
     * @return array
     */
    public function getLoggedMessages(): array
    {
        return $this->loggedMessages;
    }


}