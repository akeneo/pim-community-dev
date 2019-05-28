<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use Box\Spout\Writer\Common\Helper\ZipHelper;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;

class CreateArchive
{
    /** @var FilesystemInterface */
    private $logsStorage;

    /** @var string */
    private $logDirectory;

    /** @var string */
    private $tmpStorageDirectory;

    public function __construct(
        FilesystemInterface $logsStorage,
        string $logDirectory,
        string $tmpStorageDirectory
    ) {
        $this->logDirectory = $logDirectory;
        $this->tmpStorageDirectory = $tmpStorageDirectory;
        $this->logsStorage = $logsStorage;
    }

    public function create(): \SplFileInfo
    {
        $tmpLogsPath = $this->tmpStorageDirectory.DIRECTORY_SEPARATOR.'pim_authentication_logs';
        (new Filesystem())->mkdir($tmpLogsPath);
        $zipFilePath = tempnam($tmpLogsPath, 'logs');
        $archive = new \ZipArchive();
        if (!$archive->open($zipFilePath, \ZipArchive::CREATE)) {
            throw new \RuntimeException('The zip file cannot be opened');
        }

        $this->addReadmeFileToArchive($archive);
        $this->addLogsFileToArchive($archive);

        $archive->close();

        return new \SplfileInfo($zipFilePath);
    }

    private function addReadmeFileToArchive(\ZipArchive $archive): void
    {
        $readmeContent = <<<README
Several authentication logs file could be present in this archive.

Last logs are in the "authentication.log" file.

The other files suffixed by a date are older errors but could be useful for debugging.
Those older files are automatically [log rotated](https://symfony.com/doc/3.4/cookbook/logging/monolog.html#how-to-rotate-your-log-files), we keep only 10 days of logs by default.

Why this archive only contains only this README.txt file ?
==========================================================

If archive only contains this README file, no error occured regarding the Service Provider.
 
If you encounter troubles regarding the SSO process you'll have to check the IDP server logs.

README;

        $archive->addFromString('README.txt', $readmeContent);
    }

    private function addLogsFileToArchive(\ZipArchive $archive): void
    {
        $logs = $this->logsStorage->listContents('/saml');
        foreach ($logs as $logFile) {
            $logContent = $this->logsStorage->read($logFile['path']);
            $archive->addFromString($logFile['basename'], $logContent);
        }
    }
}
