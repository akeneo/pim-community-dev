<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\Query\FindVariantProductCompletenessInterface;
use Pim\Component\Catalog\ProductModel\ReadModel\VariantProductCompleteness;

/**
 * Query variant product completenesses to build the complete variant product ratio on the PMEF
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindVariantProductCompleteness implements FindVariantProductCompletenessInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $fromClassname;

    /**
     * FindVariantProductCompleteness constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $fromClassname
     */
    public function __construct(EntityManagerInterface $entityManager, string $fromClassname)
    {
        $this->entityManager = $entityManager;
        $this->fromClassname = $fromClassname;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $channel
     * @param string                $locale
     *
     * @return VariantProductCompleteness
     */
    public function __invoke(
        ProductModelInterface $productModel,
        string $channel = '',
        string $locale = ''
    ): VariantProductCompleteness {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('channel.code as ch, locale.code as lo, variant_product.identifier as pr, CASE WHEN (completeness.ratio = 100) THEN 1 ELSE 0 END as co')
            ->from($this->fromClassname, 'product_model')
            ->innerJoin('product_model.products', 'variant_product')
            ->innerJoin('variant_product.completenesses', 'completeness')
            ->innerJoin('completeness.locale', 'locale')
            ->innerJoin('completeness.channel', 'channel')
        ;

        if (2 === $productModel->getFamilyVariant()->getNumberOfLevel()){
            $queryBuilder->innerJoin('product_model.parent', 'root_product_model')
                ->where('root_product_model.id = :product_model')
                ->setParameter(':product_model', $productModel->getId())
            ;
        } else {
            $queryBuilder->where('product_model.id = :product_model')
                ->setParameter(':product_model', $productModel->getId())
            ;
        }

        if (!empty($channel)) {
            $queryBuilder->andWhere('channel.code = :channel')
                ->setParameter(':channel', $channel);
        }

        if (!empty($locale)) {
            $queryBuilder->andWhere('locale.code= :locale')
                ->setParameter(':locale', $locale);
        }

        $result = $queryBuilder->getQuery()->getArrayResult();

        return new VariantProductCompleteness($result);
    }
}
