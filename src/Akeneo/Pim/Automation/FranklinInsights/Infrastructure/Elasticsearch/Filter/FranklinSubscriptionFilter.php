<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

class FranklinSubscriptionFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(array $supportedFields = [], array $supportedOperators = [])
    {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!is_bool($value)) {
            throw InvalidPropertyTypeException::booleanExpected($field, static::class, $value);
        }

        if (true === $value) {
            $clause = [
                'term' => [
                    $field => true,
                ],
            ];

            $this->searchQueryBuilder->addFilter($clause);
        } else {
            $clause = [
                [
                    'term' => [
                        $field => false,
                    ],
                ],
                [
                    'bool' => [
                        'must_not' => [
                            'exists' => [
                                'field' => $field,
                            ],
                        ],
                    ],
                ],
            ];

            $this->searchQueryBuilder->addShould($clause);
        }

        return $this;
    }
}
