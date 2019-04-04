<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle\Doctrine\ORM\Query;

use Akeneo\Tool\Component\FileStorage\Query\FindKeyByHashAndNameQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * Find a FileInfo model by its hash value
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindKeyByHashAndNameQuery implements FindKeyByHashAndNameQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchKey(string $hash, string $originalFilename): ?string
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('file_key')
            ->from('akeneo_file_storage_file_info')
            ->where('hash = :hash')
            ->andWhere('original_filename = :filename')
            ->setParameter(':hash', $hash, \PDO::PARAM_STR)
            ->setParameter(':filename', $originalFilename, \PDO::PARAM_STR);

        $fileKey = $qb->execute()->fetchColumn();

        return empty($fileKey) ? null : $fileKey;
    }
}
