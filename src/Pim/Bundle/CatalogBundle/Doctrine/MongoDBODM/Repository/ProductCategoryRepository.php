<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Akeneo\Bundle\ClassificationBundle\Doctrine\Mongo\Repository\AbstractItemCategoryRepository;
use Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface;

/**
 * Product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRepository extends AbstractItemCategoryRepository implements ProductCategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyFilterByIds($qb, array $productIds, $include)
    {
        if ($include) {
            $qb->addAnd($qb->expr()->field('id')->in($productIds));
        } else {
            $qb->addAnd($qb->expr()->field('id')->notIn($productIds));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('c')
            ->from($this->categoryClass, 'c', 'c.id')
            ->where('c.code = :code')
            ->setParameter('code', $identifier);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
