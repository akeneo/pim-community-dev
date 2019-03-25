<?php

namespace Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;

/**
 * Find a FileInfo model by its hash value
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindKeyByHashQuery
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchKey(string $hash): ?string
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('file_key')
            ->from('akeneo_file_storage_file_info')
            ->where('hash = :hash')
            ->setParameter(':hash', $hash, \PDO::PARAM_STR);

        $fileKey = $qb->execute()->fetchColumn();

        return empty($fileKey) ? null : $fileKey;
    }
}
