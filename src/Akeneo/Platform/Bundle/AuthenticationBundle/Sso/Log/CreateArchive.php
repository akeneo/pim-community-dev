<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use Box\Spout\Writer\Common\Helper\ZipHelper;
use Symfony\Component\Filesystem\Filesystem;

class CreateArchive
{
    /** @var string */
    private $logDirectory;

    /** @var string */
    private $tmpStorageDirectory;

    public function __construct(string $logDirectory, string $tmpStorageDirectory)
    {
        $this->logDirectory = $logDirectory;
        $this->tmpStorageDirectory = $tmpStorageDirectory;
    }

    public function create(): \SplFileInfo
    {
        $archive = new ZipHelper($this->getLogArchiveDirectory());

        $this->addReadmeFileToArchive($archive);
        $this->addLogsFileToArchive($archive);

        $archive->closeArchiveAndCopyToStream(fopen($archive->getZipFilePath(), 'wb+'));

        return new \SplfileInfo($archive->getZipFilePath());
    }

    private function getLogArchiveDirectory(): string
    {
        $logArchiveDirectory = $this->tmpStorageDirectory . DIRECTORY_SEPARATOR . 'pim_authentication_logs';

        (new Filesystem())->mkdir($logArchiveDirectory);

        return $logArchiveDirectory;
    }

    private function addReadmeFileToArchive(ZipHelper $archive): void
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

        $readmeFile = fopen($this->getLogArchiveDirectory() . DIRECTORY_SEPARATOR . 'README.txt', 'wb+');
        fwrite($readmeFile, $readmeContent);
        fclose($readmeFile);


        $archive->addFileToArchive($this->getLogArchiveDirectory(), 'README.txt');
    }

    private function addLogsFileToArchive(ZipHelper $archive): void
    {
        $logs = new \GlobIterator($this->logDirectory . DIRECTORY_SEPARATOR . 'authentication*.log');

        if($logs->count() > 0)
        {
            $logDirectory = $this->logDirectory;
            array_map(function(\SplFileInfo $logFile) use ($logDirectory, $archive){
                $archive->addFileToArchive($logDirectory, $logFile->getFilename());
            }, iterator_to_array($logs));
        }
    }
}
