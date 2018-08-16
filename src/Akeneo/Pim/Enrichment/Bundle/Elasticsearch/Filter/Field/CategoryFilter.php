<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;

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
            if (!isset($options['type_checking']) || $options['type_checking']) {
                $this->checkValue($field, $value);
            }
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
                $clause = [
                    'bool' => [
                        'should' => [
                            [
                                'terms' => [
                                    'categories' => $value
                                ]
                            ],
                            [
                                'bool' => [
                                    'must_not' => [
                                        'exists' => ['field' => 'categories']
                                    ]
                                ]
                            ]
                        ],
                        'minimum_should_match' => 1,
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);
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
     */
    protected function checkValue($field, $values): void
    {
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
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
