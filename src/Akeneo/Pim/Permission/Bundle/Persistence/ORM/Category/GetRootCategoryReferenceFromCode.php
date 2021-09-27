<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class GetRootCategoryReferenceFromCode
{
    private Connection $connection;
    private Registry $doctrine;
    private string $class;

    public function __construct(
        Connection $connection,
        Registry $doctrine,
        string $class
    ) {
        $this->connection = $connection;
        $this->doctrine = $doctrine;
        $this->class = $class;
    }

    /**
     * @return ?CategoryInterface
     */
    public function execute(string $code): ?CategoryInterface
    {
        $query = <<<SQL
SELECT id
FROM pim_catalog_category
WHERE code = :code
AND parent_id IS NULL
SQL;

        $id = $this->connection->fetchColumn($query, [
            'code' => $code,
        ]) ?: null;

        if (null === $id) {
            return null;
        }

        $em = $this->doctrine->getManager();
        if (!$em instanceof EntityManagerInterface) {
            throw new \LogicException(sprintf('Expected %s, got %s', EntityManagerInterface::class, get_class($em)));
        }

        return $em->getReference($this->class, (int) $id);
    }
}
