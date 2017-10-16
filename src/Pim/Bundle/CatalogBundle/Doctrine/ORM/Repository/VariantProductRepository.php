<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\VariantProductRepositoryInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductRepository extends ProductRepository implements VariantProductRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findSiblingsProducts(VariantProductInterface $product): array
    {
        $qb = $this
            ->createQueryBuilder('vp')
            ->where('vp.parent = :parent')
            ->setParameter('parent', $product->getParent());

        if (null !== $id = $product->getId()) {
            $qb->andWhere('vp.id != :id')->setParameter('id', $id);
        }

        return $qb->getQuery()->execute();
    }
}
