<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AncestorCodeFilter extends AbstractFieldFilter
{
    private const ANCESTOR_CODES_ES_FIELD = 'ancestors.codes';

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param array                           $supportedFields
     * @param array                           $supportedOperators
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        array $supportedFields,
        array $supportedOperators
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $values, $locale = null, $channel = null, $options = []): void
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!is_array($values)) {
            $values = [$values];
        }

        $this->checkValues($field, $values);

        switch ($operator) {
            case Operators::EQUALS:
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        self::ANCESTOR_CODES_ES_FIELD => $values,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $mustNotClause = [
                    'terms' => [
                        self::ANCESTOR_CODES_ES_FIELD => $values,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }

    private function checkValues($field, array $values): void
    {
        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
        }
    }
}
