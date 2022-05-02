<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @deprecated
 */
final class GetProductReferenceFromIdentifier
{
    public function __construct(
        private Connection $connection,
        private Registry $doctrine,
        private string $class,
    ) {
    }

    public function execute(string $identifier): ?ProductInterface
    {
        $query = <<<SQL
SELECT id
FROM pim_catalog_product
WHERE identifier = :identifier
SQL;

        $id = $this->connection->fetchOne($query, [
            'identifier' => $identifier,
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
