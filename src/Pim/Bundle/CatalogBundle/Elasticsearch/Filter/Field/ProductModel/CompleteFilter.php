<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\ProductModel;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Filter the product model which are complete or incomplete. The business rules are:
 *   - complete: A product model is displayed if at least one of its product is complete
 *   - incomplete: A product model is displayed if at least one of its product is incomplete.
 *
 * The supported operator are:
 *   - COMPLETE
 *   - INCOMPLETE
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteFilter extends AbstractFieldFilter implements FieldFilterInterface
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
     *
     * This filter only works with the product and product model index, the filter uses the following field:
     *   - at_least_complete
     *   - constant_score
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (empty($locale)) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid locale', static::class);
        }

        if (empty($channel)) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid channel', static::class);
        }

        switch ($operator) {
            case Operators::AT_LEAST_COMPLETE:
                $field = sprintf('at_least_complete.%s.%s', $channel, $locale);
                $this->searchQueryBuilder->addFilter(
                    [
                        'query' => [
                            'constant_score' => [
                                'filter' => [
                                    'term' => [
                                        $field => 1,
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
                break;

            case Operators::AT_LEAST_INCOMPLETE:
                $field = sprintf('at_least_incomplete.%s.%s', $channel, $locale);
                $this->searchQueryBuilder->addFilter(
                    [
                        'query' => [
                            'constant_score' => [
                                'filter' => [
                                    'term' => [
                                        $field => 1,
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }
}
