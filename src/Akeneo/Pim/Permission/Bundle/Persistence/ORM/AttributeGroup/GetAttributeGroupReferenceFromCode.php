<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class GetAttributeGroupReferenceFromCode
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

    public function execute(string $code): ?AttributeGroupInterface
    {
        $query = <<<SQL
SELECT id
FROM pim_catalog_attribute_group
WHERE code = :code
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

        return $em->getReference(AttributeGroup::class, (int) $id);
    }
}
