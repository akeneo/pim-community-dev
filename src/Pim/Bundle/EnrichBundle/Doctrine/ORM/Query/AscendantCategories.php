<?php
declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Enrich\Query\AscendantCategoriesInterface;

/**
 * Query data to get the ascendant categories of entities with family variant
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AscendantCategories implements AscendantCategoriesInterface
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
        } elseif ($entity instanceof VariantProductInterface) {
            $queryBuilder
                ->select('category.id AS id, parent_category.id AS parent_id')
                ->from(VariantProductInterface::class, 'variant_product')
                ->innerJoin('variant_product.parent', 'product_model')
                ->leftJoin('product_model.parent', 'parent_product_model')
                ->leftJoin('product_model.categories', 'category')
                ->leftJoin('parent_product_model.categories', 'parent_category')
                ->where('variant_product.id = :id')
                ->setParameter(':id', $entity->getId());

            foreach ($queryBuilder->getQuery()->getResult() as $resultItem) {
                if (null !== $resultItem['id'] && !in_array(intval($resultItem['id']), $result)) {
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
