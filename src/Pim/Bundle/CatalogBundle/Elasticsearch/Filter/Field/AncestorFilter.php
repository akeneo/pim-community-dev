<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * An ancestor is a product model that is either a parent or a grand parent.
 * Look for documents having the given ancestor(s).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AncestorFilter extends AbstractFieldFilter
{
    private const ANCESTOR_ID_ES_FIELD = 'ancestors.ids';

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

        if (!$this->supportsOperator($operator)) {
            throw InvalidOperatorException::notSupported($operator, AncestorFilter::class);
        }

        $this->checkValues($values);

        switch ($operator) {
            case Operators::IN_LIST:
                $this->searchQueryBuilder->addShould(
                    [
                        [
                            'terms' => [
                                self::ANCESTOR_ID_ES_FIELD => $values,
                            ],
                        ]
                    ]
                );
                break;
            case Operators::NOT_IN_LIST:
                $mustNotClause = [
                    'terms' => [
                        self::ANCESTOR_ID_ES_FIELD => $values,
                    ],
                ];
                $filterClause = [
                    'exists' => [
                        'field' => self::ANCESTOR_ID_ES_FIELD,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;
        }
    }

    /**
     * Checks the value we want to filter on is valid
     *
     * @param $values
     *
     * @throws ObjectNotFoundException
     */
    private function checkValues($values): void
    {
        FieldFilterHelper::checkArray(self::ANCESTOR_ID_ES_FIELD, $values, static::class);
        foreach ($values as $value) {
            FieldFilterHelper::checkString(self::ANCESTOR_ID_ES_FIELD, $value, static::class);
            if (!$this->isValidId($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object "product model" with ID "%s" does not exist', $value)
                );
            }
        }
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isValidId(string $value): bool
    {
        $id = str_replace('product_model_', '', $value);

        return null !== $this->productModelRepository->findOneBy(['id' => $id]);
    }
}
