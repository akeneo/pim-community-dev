<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * Parent filter for an Elasticsearch query
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017  Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ParentFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param array                           $supportedFields
     * @param array                           $supportedOperators
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

//        if ($operator === Operators::IN_LIST) {
//            $this->checkValue($field, $value);
//        }

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        $field => $value,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $field,
                    ],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
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
     *
     * @throws ObjectNotFoundException
     */
    protected function checkValue($field, $values): bool
    {
        FieldFilterHelper::checkArray($field, $values, static::class);
        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
            if (null === $this->productModelRepository->findOneByIdentifier($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object "product model" with code "%s" does not exist', $value)
                );
            }
        }
    }
}
