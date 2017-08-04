<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Filter that searches Elasticsearch documents containing the given attributes ($value)
 * for the key "attributes" ($field).
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WithAttributesFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array                        $supportedFields
     * @param array                        $supportedOperators
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->attributeRepository = $attributeRepository;
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

        $this->checkValue($field, $value);

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        $field => $value,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        $field => $value,
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
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
            if (null === $this->attributeRepository->findOneByIdentifier($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object "attribute" with code "%s" does not exist', $value)
                );
            }
        }
    }
}
