<?php

namespace Oro\Bundle\BatchBundle\Monolog\Handler;

use Monolog\Handler\StreamHandler;

/**
 * Write the log into a separate log file
 *
 */
class BatchLogHandler extends StreamHandler
{
    /** @var string */
    protected $filename;

    /** @var string */
    protected $logDir;

    /**
     * @param string $logDir
     */
    public function __construct($logDir)
    {

        $this->logDir = $logDir;

        parent::__construct(false);
    }

    /**
     * Get the filename of the log file
     */
    public function getFilename()
    {
        return $this->url;
    }

    public function setSubDirectory($subDirectory)
    {
        $this->url = $this->getRealPath($subDirectory, $this->generateLogFilename());
    }

    /**
     * Get the real path of the log file
     *
     * @param string $subDirectory
     * @param string $filename
     *
     * @return string
     */
    public function getRealPath($subDirectory, $filename)
    {
        return sprintf('%s/%s/%s', $this->logDir, $subDirectory, $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $record)
    {
        if (!is_dir(dirname($this->url))) {
            mkdir(dirname($this->url), 0755, true);
        }

        if (!$this->url) {
            throw new \LogicException(
                'Missing stream url, the stream can not be opened. ' .
                'This may be caused by a premature call to close() or a missing sub directory configuration.'
            );
        }

        parent::write($record);
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
