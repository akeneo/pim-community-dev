<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;

/**
 * An ancestor is a product model that is either a parent or a grand parent.
 * Look for documents having the given ancestor(s).
 *
 * Imagine the following tree:
 *      RPM
 *         \PM1
 *            \P11
 *            \P12
 *         \PM2
 *            \P21
 *
 * Using this filter with "IN LIST PM1" would return:
 *         \PM1
 *            \P11
 *            \P12
 *
 * Contrary to the ancestor filter, here PM1 itself is as well returned.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelfAndAncestorFilterLabelOrIdentifier extends AbstractFieldFilter
{
    public function __construct(array $supportedFields, array $supportedOperators)
    {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = []): void
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!$this->supportsOperator($operator)) {
            throw InvalidOperatorException::notSupported($operator, SelfAndAncestorFilterLabelOrIdentifier::class);
        }

        $this->checkValue($value);

        $clauses[] = [
            'wildcard' => [
                'ancestors.codes' => sprintf('*%s*', $this->escapeValue($value)),
            ]
        ];
        $clauses[] = [
            'wildcard' => [
                'identifier' => sprintf('*%s*', $this->escapeValue($value)),
            ]
        ];

        if (null !== $channel && null !== $locale) {
            $clauses[] = [
                'wildcard' => [
                    sprintf('ancestors.labels.%s.%s', $channel, $locale) => sprintf(
                        '*%s*',
                        $this->escapeValue($value)
                    ),
                ]
            ];
            $clauses[] = [
                'wildcard' => [
                    sprintf('label.%s.%s', $channel, $locale) => sprintf(
                        '*%s*',
                        $this->escapeValue($value)
                    ),
                ]
            ];
        }

        if (null !== $channel) {
            $clauses[] = [
                'wildcard' => [
                    sprintf('ancestors.labels.%s.<all_locales>', $channel) => sprintf(
                        '*%s*',
                        $this->escapeValue($value)
                    ),
                ]
            ];
            $clauses[] = [
                'wildcard' => [
                    sprintf('label.%s.<all_locales>', $channel) => sprintf(
                        '*%s*',
                        $this->escapeValue($value)
                    ),
                ]
            ];
        }

        if (null !== $locale) {
            $clauses[] = [
                'wildcard' => [
                    sprintf('ancestors.labels.<all_channels>.%s', $locale) => sprintf(
                        '*%s*',
                        $this->escapeValue($value)
                    ),
                ]
            ];
            $clauses[] = [
                'wildcard' => [
                    sprintf('label.<all_channels>.%s', $locale) => sprintf(
                        '*%s*',
                        $this->escapeValue($value)
                    ),
                ]
            ];
        }

        $clauses[] = [
            'wildcard' => [
                'ancestors.labels.<all_channels>.<all_locales>' => sprintf('*%s*', $this->escapeValue($value)),
            ]
        ];
        $clauses[] = [
            'wildcard' => [
                'label.<all_channels>.<all_locales>' => sprintf('*%s*', $this->escapeValue($value)),
            ]
        ];

        $this->searchQueryBuilder->addFilter(
            [
                'bool' => [
                    'should' => $clauses,
                    'minimum_should_match' => 1,
                ],
            ]
        );
    }

    /**
     * @param string $value
     */
    protected function checkValue(string $value): void
    {
        FieldFilterHelper::checkString('self_and_ancestor.label_or_identifier', $value, static::class);
    }

    /**
     * Escapes particular values prior than doing a search query escaping whitespace or newlines.
     *
     * This is useful when using ES 'query_string' clauses in a search query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#_reserved_characters
     *
     * TODO: TIP-706 - This may move somewhere else
     *
     * @param string $value
     *
     * @return string
     */
    protected function escapeValue(string $value): string
    {
        $regex = '#[-+=|! &(){}\[\]^"~*<>?:/\\\]#';

        return preg_replace($regex, '\\\$0', $value);
    }
}
