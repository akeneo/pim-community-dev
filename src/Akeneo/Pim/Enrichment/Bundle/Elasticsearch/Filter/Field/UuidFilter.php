<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UuidFilter extends AbstractFieldFilter
{
    public function __construct(private string $prefix)
    {
        $this->supportedFields = ['uuid'];
        $this->supportedOperators = [Operators::IN_LIST, Operators::NOT_IN_LIST];
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter(
        $field,
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

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        'id' => \array_map(
                            fn (string $uuid): string => \sprintf('%s%s', $this->prefix, \mb_strtolower($uuid)),
                            $value
                        ),
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        'id' => \array_map(
                            fn (string $uuid): string => \sprintf('%s%s', $this->prefix, \mb_strtolower($uuid)),
                            $value
                        ),
                    ],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, self::class);
        }

        return $this;
    }

    private function checkValue($operator, $value): void
    {
        if (Operators::IN_LIST === $operator || Operators::NOT_IN_LIST === $operator) {
            FieldFilterHelper::checkArrayOfStrings('uuid', $value, self::class);
        }
    }
}
