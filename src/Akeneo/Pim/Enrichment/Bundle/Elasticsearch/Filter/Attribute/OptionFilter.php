<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Option filter for an Elasticsearch query
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeOptionRepositoryInterface */
    protected $attributeOptionRepository;

    /**
     * @param AttributeValidatorHelper  $attrValidatorHelper
     * @param AttributeOptionRepository $attributeOptionRepository
     * @param array                     $supportedAttributeTypes
     * @param array                     $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        AttributeOptionRepositoryInterface $attributeOptionRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $values,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkLocaleAndChannel($attribute, $locale, $channel);

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $values);
        }

        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        $attributePath => $values,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_IN_LIST:
                $mustNotClause = [
                    'terms' => [
                        $attributePath => $values,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Check if values are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $values
     *
     * @throws ObjectNotFoundException
     */
    protected function checkValue(AttributeInterface $attribute, $values)
    {
        FieldFilterHelper::checkArray($attribute->getCode(), $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $value, static::class);
        }

        $attributeOptions = $this->attributeOptionRepository->findCodesByIdentifiers($attribute->getCode(), $values);
        $optionCodes = array_map(
            function ($attributeOptions) {
                return $attributeOptions['code'];
            },
            $attributeOptions
        );

        $unexistingValues = array_diff($values, $optionCodes);
        if (count($unexistingValues) > 0) {
            throw new ObjectNotFoundException(
                sprintf(
                    'Object "%s" with code "%s" does not exist',
                    $attribute->getBackendType(),
                    reset($unexistingValues)
                )
            );
        }
    }
}
