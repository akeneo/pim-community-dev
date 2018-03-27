<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Filter in/complete product model and product depending their completeness (variant product) or if it has at least
 * one in/complete variant product (product model).
 *
 * The supported operator are:
 *   - AT_LEAST_COMPLETE
 *   - AT_LEAST_INCOMPLETE
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends AbstractFieldFilter implements FieldFilterInterface
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
        if (empty($locale)) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid locale', static::class);
        }

        if (empty($channel)) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid channel', static::class);
        }

        $productFilterField = sprintf('completeness.%s.%s', $channel, $locale);

        switch ($operator) {
            case Operators::AT_LEAST_COMPLETE:
                $productModelFilterField = sprintf('at_least_complete.%s.%s', $channel, $locale);
                $this->searchQueryBuilder->addFilter(
                    [
                        'bool' => [
                            'should' => [
                                ['term' => [$productFilterField => 100]],
                                ['term' => [$productModelFilterField => 1]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ]
                );
                break;

            case Operators::AT_LEAST_INCOMPLETE:
                $productModelFilterField = sprintf('at_least_incomplete.%s.%s', $channel, $locale);
                $this->searchQueryBuilder->addFilter(
                    [
                        'bool' => [
                            'should' => [
                                ['range' => [$productFilterField => ['lt' => 100]]],
                                ['term' => [$productModelFilterField => 1]],
                            ],
                            'minimum_should_match' => 1,
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
