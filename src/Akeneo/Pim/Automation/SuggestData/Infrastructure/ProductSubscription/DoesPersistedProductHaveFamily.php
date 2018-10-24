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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\ProductSubscription;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\DoesPersistedProductHaveFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Checks if a product has a family in the data stored in MySQL.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DoesPersistedProductHaveFamily implements DoesPersistedProductHaveFamilyInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function check(ProductInterface $product): bool
    {
        $productTableName = $this->entityManager->getClassMetadata(ProductInterface::class)->getTableName();

        $query = <<<SQL
SELECT family_id
FROM $productTableName
WHERE id = :id 
SQL;
        $statement = $this->entityManager->getConnection()->executeQuery($query, ['id' => $product->getId()]);
        $result = $statement->fetch();

        return null !== $result['family_id'];
    }
}
