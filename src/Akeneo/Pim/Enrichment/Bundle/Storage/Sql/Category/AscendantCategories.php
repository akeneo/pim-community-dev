<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Pim\Enrichment\Component\Category\Query\AscendantCategoriesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Query data to get the ascendant categories of entities with family variant
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AscendantCategories implements AscendantCategoriesInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIds(EntityWithFamilyVariantInterface $entity): array
    {
        $result = [];
        $queryBuilder = $this->entityManager->createQueryBuilder();

        if ($entity instanceof ProductModelInterface) {
            $queryBuilder
                ->select('DISTINCT(category.id) AS id')
                ->from(ProductModelInterface::class, 'product_model')
                ->innerJoin('product_model.parent', 'parent_product_model')
                ->innerJoin('parent_product_model.categories', 'category')
                ->where('product_model.id = :id')
                ->setParameter(':id', $entity->getId());

            $result = array_map(function ($id) {
                return intval($id['id']);
            }, $queryBuilder->getQuery()->getResult());
        } elseif ($entity instanceof ProductInterface && $entity->isVariant()) {
            $queryBuilder
                ->select('category.id AS id, parent_category.id AS parent_id')
                ->from(ProductInterface::class, 'variant_product')
                ->innerJoin('variant_product.parent', 'product_model')
                ->leftJoin('product_model.parent', 'parent_product_model')
                ->leftJoin('product_model.categories', 'category')
                ->leftJoin('parent_product_model.categories', 'parent_category')
                ->where('variant_product.id = :id')
                ->setParameter(':id', $entity->getId());

            foreach ($queryBuilder->getQuery()->getResult() as $resultItem) {
                if (!in_array(intval($resultItem['id']), $result)) {
                    $result[] = intval($resultItem['id']);
                }
                if (null !== $resultItem['parent_id'] && !in_array(intval($resultItem['parent_id']), $result)) {
                    $result[] = intval($resultItem['parent_id']);
                }
            }
        }

        return $result;
    }
}
