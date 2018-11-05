<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Product;

use Akeneo\Pim\Automation\SuggestData\Domain\Query\Product\SelectProductFamilyIdQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\ORM\EntityManager;

/**
 * Checks if a product has a family in the data stored in MySQL.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectProductFamilyIdQuery implements SelectProductFamilyIdQueryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(int $productId): ?int
    {
        $query = <<<SQL
SELECT family_id
FROM pim_catalog_product
WHERE id = :product_id 
SQL;
        $bindParams = [
            'tableName' => $this->entityManager->getClassMetadata(ProductInterface::class)->getTableName(),
            'product_id' => $productId,
        ];
        $statement = $this->entityManager->getConnection()->executeQuery($query, $bindParams);
        $result = $statement->fetch();

        return (null === $result['family_id']) ? null : (int) $result['family_id'];
    }
}
