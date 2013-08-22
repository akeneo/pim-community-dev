<?php

namespace Pim\Bundle\BatchBundle\Monolog\Handler;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Write the log into a separate log file
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchLogHandler extends StreamHandler
{
    public function __construct($rootDir, $logDir)
    {
        $this->filename = $this->generateLogFilename($logDir);

        parent::__construct(sprintf('%s/../web/%s', $rootDir, $this->filename));
    }

    public function getFilename()
    {
        return $this->filename;
    }

    private function generateLogFilename($logDir)
    {
        $hash = sha1(uniqid(rand(), true));

        return sprintf('%s/batch_%s.log', rtrim($logDir, '/'), $hash);
    }
}
