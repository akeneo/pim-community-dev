<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\Counter;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

/**
 * Category product counter, using a PQB.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryProductsCounter implements CategoryItemsCounterInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param CategoryRepositoryInterface         $categoryRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        $categoryCodes = $inChildren
            ? $this->categoryRepository->getAllChildrenCodes($category, $inProvided)
            : [$category->getCode()];

        $options = [
            'filters' => [
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value' => $categoryCodes
                ]
            ]
        ];

        $pqb = $this->pqbFactory->create($options);
        $items = $pqb->execute();

        return $items->count();
    }
}
