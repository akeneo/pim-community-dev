<?php

namespace Pim\Bundle\BatchBundle\Monolog\Handler;

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

    protected $filename;

    public function __construct($logDir)
    {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logDir   = $logDir;
        $this->filename = $this->generateLogFilename();

        parent::__construct($this->getRealPath($this->filename));
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getRealPath($filename)
    {
        return sprintf('%s/%s', $this->logDir, $filename);
    }

    private function generateLogFilename()
    {
        return sprintf('batch_%s.log', sha1(uniqid(rand(), true)));
    }
}
