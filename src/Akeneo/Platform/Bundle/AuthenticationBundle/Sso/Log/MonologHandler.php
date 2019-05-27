<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

final class MonologHandler extends RotatingFileHandler
{
    /** @var MountManager */
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

        $this->logStorage->put($this->url, $record['formatted']);
    }
}
