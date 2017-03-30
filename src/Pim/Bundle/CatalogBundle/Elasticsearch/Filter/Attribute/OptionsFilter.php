<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Attribute;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Options filter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeOptionRepository */
    protected $attributeOptionRepository;

    /**
     * @param AttributeValidatorHelper  $attributeValidatorHelper
     * @param AttributeOptionRepository $attributeOptionRepository
     * @param array                     $supportedAttributeTypes
     * @param array                     $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attributeValidatorHelper,
        AttributeOptionRepository $attributeOptionRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attributeValidatorHelper;
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
        $scope = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkLocaleAndChannel($attribute, $locale, $scope);

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $values);
        }

        $attributePath = $this->getAttributePath($attribute, $locale, $scope);

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        $attributePath => $values,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $notInListClause = [
                    'terms' => [
                        $attributePath => $values,
                    ],
                ];

                $existsClause = [
                    'exists' => ['field' => $attributePath],
                ];

                $this->searchQueryBuilder->addMustNot($notInListClause);
                $this->searchQueryBuilder->addFilter($existsClause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => ['field' => $attributePath],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => ['field' => $attributePath],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $values
     *
     * @throws ObjectNotFoundException
     */
    protected function checkValue(AttributeInterface $attribute, $values)
    {
        FieldFilterHelper::checkArray($attribute->getCode(), $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $value, static::class);
        }

        $attributeOptions = $this->attributeOptionRepository->findByIdentifiers($attribute->getCode(), $values);
        $optionCodes = array_map(
            function ($attributeOptions) {
                return $attributeOptions['code'];
            },
            $attributeOptions
        );

        $unexistingOptionCodes = array_diff($values, $optionCodes);
        if (count($unexistingOptionCodes) > 0) {
            throw new ObjectNotFoundException(
                sprintf(
                    'Object "%s" with code "%s" does not exist',
                    AttributeTypes::BACKEND_TYPE_OPTIONS,
                    reset($unexistingOptionCodes)
                )
            );
        }
    }
}
