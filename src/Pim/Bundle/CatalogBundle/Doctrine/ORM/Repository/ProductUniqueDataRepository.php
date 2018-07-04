<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface;

/**
 * Product unique data repository. Please see {@see Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface}
 * for more information.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUniqueDataRepository extends EntityRepository implements ProductUniqueDataRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function uniqueDataExistsInAnotherProduct(ValueInterface $value, ProductInterface $product)
    {
        $queryBuilder = $this->createQueryBuilder('ud')
            ->select('COUNT(ud)')
            ->where('ud.attribute = :attribute')
            ->andWhere('ud.rawData = :data')
        ;

        $parameters = [
            'attribute' => $value->getAttribute(),
            'data' => $value->__toString(),
        ];

        if (null !== $product->getId()) {
            $queryBuilder->andWhere('ud.product != :product');
            $parameters['product'] = $product;
        }

        $queryBuilder->setParameters($parameters);

        $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();

        return 0 !== $count;
    }
}
