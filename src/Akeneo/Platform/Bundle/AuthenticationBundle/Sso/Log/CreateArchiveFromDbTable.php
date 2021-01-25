<?php
declare(strict_types=1);

/*
 * this file is part of the akeneo pim enterprise edition.
 *
 * (c) 2014 akeneo sas (http://www.akeneo.com)
 *
 * for the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */
namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use Doctrine\DBAL\Connection;

/**
 * This service generates a ZIP archive with the content of the log table
 */
final class CreateArchiveFromDbTable extends CreateArchive
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $tmpStorageDirectory;

    /** @static string */
    const LOG_FILENAME = 'sso.log';

    public function __construct(Connection $connection, string $tmpStorageDirectory)
    {
        $this->connection = $connection;
        $this->tmpStorageDirectory = $tmpStorageDirectory;
    }

    public function create(): \SplFileInfo
    {
        $zipFilePath = tempnam($this->tmpStorageDirectory, 'pim_authentication_logs') . '.zip';

        $archive = new \ZipArchive();
        if (!$archive->open($zipFilePath, \ZipArchive::CREATE)) {
            throw new \RuntimeException('The ZIP file cannot be created.');
        }

        $archive->addFromString(self::LOG_FILENAME, $this->getLogContent());

        $archive->close();

        return new \SplfileInfo($zipFilePath);
    }

    private function getLogContent(): string
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist([DbTableLogHandler::TABLE_NAME])) {
            return "";
        }

        $logContent = "";

        $query = sprintf('SELECT message FROM %s ORDER BY time ASC', DbTableLogHandler::TABLE_NAME);

        $results = $this->connection->executeQuery($query);

        while ($logEntry = $results->fetchColumn(0)) {
            $logContent .= $logEntry;
        }

        return $logContent;
    }
}
