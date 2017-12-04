<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * An ancestor is a product model that is either a parent or a grand parent.
 * Look for documents having the given ancestor(s).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelfAndAncestorFilter extends AbstractFieldFilter
{
    private const ANCESTOR_ID_ES_FIELD = 'ancestors.ids';

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param array                           $supportedFields
     * @param array                           $supportedOperators
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        ProductRepositoryInterface $productRepository,
        array $supportedFields,
        array $supportedOperators
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productRepository = $productRepository;
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
            throw InvalidOperatorException::notSupported($operator, SelfAndAncestorFilter::class);
        }

        $this->checkValues($values);

        switch ($operator) {
            case Operators::IN_LIST:
                $ancestorClause = [
                    'terms' => [
                        'id' => $values,
                    ],
                ];
                $ancestorsClause = [
                    'terms' => [
                        self::ANCESTOR_ID_ES_FIELD => $values,
                    ],
                ];
                $this->searchQueryBuilder->addShould($ancestorClause);
                $this->searchQueryBuilder->addShould($ancestorsClause);
                break;
            case Operators::NOT_IN_LIST:
                $ancestorClause = [
                    'terms' => [
                        self::ANCESTOR_ID_ES_FIELD => $values,
                    ],
                ];
                $selfClause = [
                    'terms' => [
                        'id' => $values,
                    ]
                ];
                $this->searchQueryBuilder->addMustNot($selfClause);
                $this->searchQueryBuilder->addMustNot($ancestorClause);
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
            if (!$this->isValidProductModelId($value) && !$this->isValidProductId($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object with ID "%s" does not exist as a product nor as a product model', $value)
                );
            }
        }
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isValidProductModelId(string $value): bool
    {
        if (0 !== strpos($value, 'product_model_')) {
            return false;
        }

        $id = str_replace('product_model_', '', $value);

        return null !== $this->productModelRepository->findOneBy(['id' => $id]);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isValidProductId(string $value): bool
    {
        if (0 !== strpos($value, 'product_')) {
            return false;
        }

        $id = str_replace('product_', '', $value);

        return null !== $this->productRepository->findOneBy(['id' => $id]);
    }
}
