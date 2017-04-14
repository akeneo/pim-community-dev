<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Product category filter.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array                       $supportedFields
     * @param array                       $supportedOperators
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if ($operator !== Operators::UNCLASSIFIED) {
            $this->checkValue($field, $value);
        }

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        'categories' => $value
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        'categories' => $value
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IN_CHILDREN_LIST:
                $childrenCategoryCodes = $this->getAllChildrenCodes($value);
                $clause = [
                    'terms' => [
                        'categories' => $childrenCategoryCodes
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_IN_CHILDREN_LIST:
                $childrenCategoryCodes = $this->getAllChildrenCodes($value);
                $clause = [
                    'terms' => [
                        'categories' => $childrenCategoryCodes
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::UNCLASSIFIED:
                $clause = [
                    'exists' => ['field' => 'categories']
                ];
                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IN_LIST_OR_UNCLASSIFIED:
                $shouldClauseInList = [
                    'terms' => [
                        'categories' => $value
                    ],
                ];
                $shouldClauseUnclassified = [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'categories']
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addShould($shouldClauseInList);
                $this->searchQueryBuilder->addShould($shouldClauseUnclassified);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     *
     * @throws ObjectNotFoundException
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
            if (null === $this->categoryRepository->findOneByIdentifier($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object "category" with code "%s" does not exist', $value)
                );
            }
        }
    }

    /**
     * Get children category ids
     *
     * @param integer[] $categoryCodes
     *
     * @return integer[]
     */
    protected function getAllChildrenCodes(array $categoryCodes)
    {
        $allChildrenCodes = [];
        foreach ($categoryCodes as $categoryCode) {
            $category = $this->categoryRepository->findOneBy(['code' => $categoryCode]);
            $childrenCodes = $this->categoryRepository->getAllChildrenCodes($category);
            $childrenCodes[] = $category->getCode();
            $allChildrenCodes = array_merge($allChildrenCodes, $childrenCodes);
        }

        return $allChildrenCodes;
    }
}
