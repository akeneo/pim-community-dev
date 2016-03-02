<?php

namespace Akeneo\Bundle\BatchBundle\Monolog\Handler;

use Monolog\Handler\StreamHandler;

/**
 * Write the log into a separate log file
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->url;
    }

    /**
     * @param string $subDirectory
     */
    public function setSubDirectory($subDirectory)
    {
        $this->url = $this->getRealPath($this->generateLogFilename(), $subDirectory);
    }

    /**
     * Get the real path of the log file
     *
     * @param string $filename
     * @param string $subDirectory
     *
     * @return string
     *
     * @deprecated
     */
    public function getRealPath($filename, $subDirectory = null)
    {
        if ($subDirectory) {
            return sprintf('%s/%s/%s', $this->logDir, $subDirectory, $filename);
        }

        return sprintf('%s/%s', $this->logDir, $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $record)
    {
        if (!$this->url) {
            $this->url = $this->getRealPath($this->generateLogFilename());
        }

        if (!is_dir(dirname($this->url))) {
            mkdir(dirname($this->url), 0755, true);
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
