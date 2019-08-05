<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductMassActionRepositoryInterface;
use Doctrine\ORM\EntityManager;

/**
 * Mass action repository for product entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassActionRepository implements ProductMassActionRepositoryInterface
{
    /** @var string */
    protected $entityName;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductQueryBuilder $queryBuilder
     */
    public function applyMassActionParameters($queryBuilder, $inset, array $values)
    {
        if (!empty($values)) {
            $inset ? $this->includeProducts($queryBuilder, $values) : $this->excludeProducts($queryBuilder, $values);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $identifiers)
    {
        if (empty($identifiers)) {
            throw new \LogicException('No products to remove');
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->delete($this->entityName, 'p')
            ->where($qb->expr()->in('p.id', $identifiers));

        return $qb->getQuery()->execute();
    }

    /**
     * Apply filters to include the products selected for the mass action
     */
    private function includeProducts(ProductQueryBuilderInterface $queryBuilder, array $productIds): void
    {
        $queryBuilder->addFilter('id', Operators::IN_LIST, $productIds);
    }

    /**
     * Apply filters to exclude the products unselected for the mass action.
     * For the product models, their variants must be excluded too.
     */
    private function excludeProducts(ProductQueryBuilderInterface $queryBuilder, array $productIds): void
    {
        $queryBuilder->addFilter('id', Operators::NOT_IN_LIST, $productIds);

        $productModelIds = array_values(array_filter($productIds, function ($id) {
            return 0 === strpos($id, 'product_model_');
        }));

        if (!empty($productModelIds)) {
            $queryBuilder->addFilter('ancestor.id', Operators::NOT_IN_LIST, $productModelIds);
        }
    }
}
