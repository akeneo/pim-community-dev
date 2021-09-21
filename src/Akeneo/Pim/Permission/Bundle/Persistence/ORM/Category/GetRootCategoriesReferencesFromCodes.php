<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category;

use Akeneo\Tool\Component\Classification\Model\Category;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class GetRootCategoriesReferencesFromCodes
{
    private Connection $connection;
    private Registry $doctrine;

    public function __construct(
        Connection $connection,
        Registry $doctrine
    ) {
        $this->connection = $connection;
        $this->doctrine = $doctrine;
    }

    /**
     * @return CategoryInterface[]
     */
    public function execute(array $codes): array
    {
        $query = <<<SQL
SELECT id
FROM pim_catalog_category
WHERE code IN (:codes)
AND parent_id IS NULL
SQL;

        $results = $this->connection->fetchAssoc($query, [
            'codes' => $codes,
        ], [
            'codes' => Connection::PARAM_STR_ARRAY,
        ]) ?: [];

        $em = $this->doctrine->getManager();
        if (!$em instanceof EntityManagerInterface) {
            throw new \LogicException(sprintf('Expected %s, got %s', EntityManagerInterface::class, get_class($em)));
        }

        return array_map(function (string $id) use ($em) {
            return $em->getReference(Category::class, (int) $id);
        }, $results);
    }
}
