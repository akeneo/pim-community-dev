<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\Query\CompletenessGridFilterInterface;
use Pim\Component\Catalog\ProductModel\Query\NormalizedCompletenessGridFilterData;

/**
 * Find data used by the datagrid completeness filter. We need to know if a product model has at least one
 * complete / incomplete variant product for each channel and locale.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessGridFilter implements CompletenessGridFilterInterface
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
    public function findNormalizedData(ProductModelInterface $productModel): NormalizedCompletenessGridFilterData
    {
        $select = <<<SELECT
channel.code AS channel_code,
locale.code AS locale_code,
CASE WHEN (completeness.ratio = 100) THEN 1 ELSE 0 END AS complete, 
CASE WHEN (completeness.ratio < 100) THEN 1 ELSE 0 END AS incomplete
SELECT;

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select($select);

        if (2 === $productModel->getFamilyVariant()->getNumberOfLevel() && $productModel->isRootProductModel()) {
            $queryBuilder
                ->from(ProductModelInterface::class, 'root_product_model')
                ->innerJoin('root_product_model.productModels', 'sub_product_model')
                ->innerJoin('sub_product_model.products', 'variant_product')
                ->where('root_product_model.id = :product_model')
            ;
        } else {
            $queryBuilder
                ->from(ProductModelInterface::class, 'sub_product_model')
                ->innerJoin('sub_product_model.products', 'variant_product')
                ->where('sub_product_model.id = :product_model')
            ;
        }

        $queryBuilder
            ->innerJoin('variant_product.completenesses', 'completeness')
            ->innerJoin('completeness.locale', 'locale')
            ->innerJoin('completeness.channel', 'channel')
            ->setParameter(':product_model', $productModel->getId());

        $result = $queryBuilder->getQuery()->getArrayResult();

        return new NormalizedCompletenessGridFilterData($result);
    }
}
