<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use League\Flysystem\FilesystemInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

final class FlySystemLogHandler extends RotatingFileHandler
{
    /** @var FilesystemInterface */
    private $logStorage;

    public function __construct(
        FilesystemInterface $logsStorage,
        $filename,
        $maxFiles = 0,
        $level = Logger::DEBUG,
        $bubble = true,
        $filePermission = null,
        $useLocking = false
    ) {
        parent::__construct($filename, $maxFiles, $level, $bubble, $filePermission, $useLocking);

        $this->logStorage = $logsStorage;
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record): void
    {
        if (null === $this->url || '' === $this->url) {
            throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
        }
        $logFileContent = '';
        if ($this->logStorage->has($this->url)) {
            $logFileContent = $this->logStorage->read($this->url);
        }

        $this->logStorage->put($this->url, $logFileContent.$record['formatted']);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTimedFilename(): string
    {
        $fileInfo = pathinfo($this->filename);
        $timedFilename = str_replace(
            array('{filename}', '{date}'),
            array($fileInfo['filename'], date($this->dateFormat)),
            '.'.DIRECTORY_SEPARATOR.'saml'.DIRECTORY_SEPARATOR.$this->filenameFormat
        );

        if (!empty($fileInfo['extension'])) {
            $timedFilename .= '.'.$fileInfo['extension'];
        }

        return $timedFilename;
    }
}
