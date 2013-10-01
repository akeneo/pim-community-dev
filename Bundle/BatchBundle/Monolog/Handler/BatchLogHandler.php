<?php

namespace Oro\Bundle\BatchBundle\Monolog\Handler;

use Monolog\Handler\StreamHandler;

/**
 * Write the log into a separate log file
 *
 */
class BatchLogHandler extends StreamHandler
{
    /**
     * @var string $filename
     */
    protected $filename;

    /**
     * @var string $logDir
     */
    protected $logDir;

    /**
     * @param string $logDir
     */
    public function __construct($logDir)
    {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logDir   = $logDir;
        $this->filename = $this->generateLogFilename();

        parent::__construct($this->getRealPath($this->filename));
    }

    /**
     * Get the filename of the log file
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the real path of the log file
     *
     * @param string $filename
     *
     * @return string
     */
    public function getRealPath($filename)
    {
        return sprintf('%s/%s', $this->logDir, $filename);
    }

    /**
     * Generates a random filename
     *
     * @return string
     */
    private function generateLogFilename()
    {
        return sprintf('batch_%s.log', sha1(uniqid(rand(), true)));
    }
}
