<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\Query\CompleteVariantProducts;
use Pim\Component\Catalog\ProductModel\Query\VariantProductRatioInterface;

/**
 * Query variant product completenesses to build the complete variant product ratio on the PMEF
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductRatio implements VariantProductRatioInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * VariantProductRatio constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $channel
     * @param string                $locale
     *
     * @return CompleteVariantProducts
     */
    public function findComplete(
        ProductModelInterface $productModel,
        string $channel = '',
        string $locale = ''
    ): CompleteVariantProducts {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select(
            'channel.code as channel_code, locale.code as locale_code, variant_product.identifier as product_identifier, CASE WHEN (completeness.ratio = 100) THEN 1 ELSE 0 END as complete'
        );

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

        if (!empty($channel)) {
            $queryBuilder->andWhere('channel.code = :channel')
                ->setParameter(':channel', $channel);
        }

        if (!empty($locale)) {
            $queryBuilder->andWhere('locale.code= :locale')
                ->setParameter(':locale', $locale);
        }

        $result = $queryBuilder->getQuery()->getArrayResult();

        return new CompleteVariantProducts($result);
    }
}
