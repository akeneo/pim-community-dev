<?php

namespace Pim\Bundle\ReferenceDataBundle\Elasticsearch\Filter\Attribute;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

/**
 * Reference data filter for an Elasticsearch query
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var ReferenceDataRepositoryResolver */
    protected $referenceDataRepositoryResolver;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /**
     * @param AttributeValidatorHelper         $attrValidatorHelper
     * @param ReferenceDataRepositoryResolver  $referenceDataRepositoryResolver
     * @param ConfigurationRegistryInterface   $registry
     * @param array                            $supportedAttributeTypes
     * @param array                            $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ReferenceDataRepositoryResolver $referenceDataRepositoryResolver,
        ConfigurationRegistryInterface $registry,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->referenceDataRepositoryResolver = $referenceDataRepositoryResolver;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
        $this->registry = $registry;
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
                $filterClause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();

        $isRegistredReferenceData = null !== $referenceDataName &&
            !empty($referenceDataName) &&
            null !== $this->registry->get($attribute->getReferenceDataName());

        return $isRegistredReferenceData;
    }

    /**
     * Check if values are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $values
     *
     * @throws InvalidPropertyException
     */
    protected function checkValue(AttributeInterface $attribute, $values)
    {
        FieldFilterHelper::checkArray($attribute->getCode(), $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $value, static::class);
        }

        $referenceDataRepository = $this->referenceDataRepositoryResolver->resolve($attribute->getReferenceDataName());
        $existingReferenceData =  $referenceDataRepository->findCodesByIdentifiers($values);
        $referenceDataCodes = array_map(
            function ($referenceData) {
                return $referenceData['code'];
            },
            $existingReferenceData
        );

        $unexistingValues = array_diff($values, $referenceDataCodes);
        if (count($unexistingValues) > 0) {
            $message = sprintf(
                'No reference data "%s" with code "%s" has been found',
                $attribute->getReferenceDataName(),
                reset($unexistingValues)
            );

            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                $message,
                $referenceDataRepository->getClassName(),
                implode(',', $values)
            );
        }
    }
}
