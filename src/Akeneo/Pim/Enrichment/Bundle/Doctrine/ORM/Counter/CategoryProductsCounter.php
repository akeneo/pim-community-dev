<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;

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
