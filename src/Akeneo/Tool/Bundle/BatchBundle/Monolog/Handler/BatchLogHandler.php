<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
     * @param int             $level          The minimum logging level at which this handler will be triggered
     * @param Boolean         $bubble         Whether the messages that are handled can bubble up the stack or not
     * @param int|null        $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param Boolean         $useLocking     Try to lock log file before doing any writes
     * @param string          $logDir         Batch log directory
     */
    public function __construct(
        $level = Logger::DEBUG,
        $bubble = true,
        $filePermission = null,
        $useLocking = false,
        $logDir
    ) {
        $this->level = $level;
        $this->bubble = $bubble;
        $this->stream = null;
        $this->url = null;
        $this->filePermission = $filePermission;
        $this->useLocking = $useLocking;
        $this->logDir = $logDir;
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
    private function getRealPath($filename, $subDirectory = null)
    {
        if (null !== $subDirectory) {
            return sprintf('%s/%s/%s', $this->logDir, $subDirectory, $filename);
        }

        return sprintf('%s/%s', $this->logDir, $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $record)
    {
        if (null === $this->url) {
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
