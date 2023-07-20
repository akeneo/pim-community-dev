<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * Label or identifier filter for an Elasticsearch query
 * It can also perform a search on the id if optional $idPrefixes is provided
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelOrIdentifierFilter extends AbstractFieldFilter
{
    public function __construct(
        private readonly GetAttributes $getAttributes,
        array $supportedFields = [],
        array $supportedOperators = [],
        private readonly array $idPrefixes = [],
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter(
        $attribute,
        $operator,
        $value,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkValue($operator, $value);

        $identifierAttributeFields = \array_map(
            static fn (AttributeInterface $attributeFromList): string =>
                \sprintf(
                    'values.%s-%s.<all_channels>.<all_locales>',
                    $attributeFromList->getCode(),
                    AttributeTypes::BACKEND_TYPE_TEXT
                ),
            $this->getAttributes->forType(AttributeTypes::IDENTIFIER)
        );

        foreach ($identifierAttributeFields as $identifierAttributeField) {
            $clauses[] = [
                'wildcard' => [
                    $identifierAttributeField => sprintf('*%s*', $this->escapeValue($value)),
                ]
            ];
        }

        $clauses[] = [
            'wildcard' => [
                'identifier' => sprintf('*%s*', $this->escapeValue($value)),
            ]
        ];

        if (null !== $channel && null !== $locale) {
            $clauses[] = [
                'wildcard' => [
                    sprintf('label.%s.%s', $channel, $locale) => sprintf('*%s*', $this->escapeValue($value)),
                ]
            ];
        }

        if (null !== $channel) {
            $clauses[] = [
                'wildcard' => [
                    sprintf('label.%s.<all_locales>', $channel) => sprintf('*%s*', $this->escapeValue($value)),
                ]
            ];
        }

        if (null !== $locale) {
            $clauses[] = [
                'wildcard' => [
                    sprintf('label.<all_channels>.%s', $locale) => sprintf('*%s*', $this->escapeValue($value)),
                ]
            ];
        }

        $clauses[] = [
            'wildcard' => [
                'label.<all_channels>.<all_locales>' => sprintf('*%s*', $this->escapeValue($value)),
            ]
        ];

        foreach ($this->idPrefixes as $idPrefix) {
            $clauses[] = [
                'term' => [
                    'id' => \sprintf('%s%s', $idPrefix, \mb_strtolower($value)),
                ],
            ];
        }

        $this->searchQueryBuilder->addFilter(
            [
                'bool' => [
                    'should' => $clauses,
                    'minimum_should_match' => 1,
                ],
            ]
        );

        return $this;
    }

    /**
     * Checks that the value is a number.
     *
     * @param string $operator
     * @param mixed  $value
     */
    protected function checkValue($operator, $value): void
    {
        FieldFilterHelper::checkString('label_or_identifier', $value, static::class);

        if (!in_array($operator, [Operators::CONTAINS])) {
            throw InvalidOperatorException::notSupported($operator, static::class);
        }
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
