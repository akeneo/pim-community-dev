<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Filter;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Proposal reference data filter for an Elasticsearch query
 */
class AssetFamilyFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var AssetExistsInterface */
    private $assetExists;

    /** @var ConfigurationRegistryInterface */
    private $assetFamilyExists;

    /**
     * @param ElasticsearchFilterValidator $filterValidator
     * @param AssetExistsInterface $assetExists
     * @param AssetFamilyExistsInterface $assetFamilyExists
     * @param array $supportedAttributeTypes
     * @param array $supportedOperators
     */
    public function __construct(
        ElasticsearchFilterValidator $filterValidator,
        AssetExistsInterface $assetExists,
        AssetFamilyExistsInterface $assetFamilyExists,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->filterValidator = $filterValidator;
        $this->assetExists = $assetExists;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
        $this->assetFamilyExists = $assetFamilyExists;
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

                $attributeInEntityClauses = [
                    [
                        'terms' => [
                            self::ATTRIBUTES_FOR_THIS_LEVEL_ES_ID => [$attribute->getCode()],
                        ],
                    ],
                    [
                        'terms' => [
                            self::ATTRIBUTES_OF_ANCESTORS_ES_ID => [$attribute->getCode()],
                        ],
                    ]
                ];
                $this->searchQueryBuilder->addFilter(
                    [
                        'bool' => [
                            'should' => $attributeInEntityClauses,
                            'minimum_should_match' => 1,
                        ],
                    ]
                );
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
        $assetFamilyIdentifier = $attribute->getReferenceDataName();

        $assetFamilyExists = null !== $assetFamilyIdentifier &&
            !empty($assetFamilyIdentifier) &&
            true === $this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));

        return $assetFamilyExists && in_array($attribute->getAttributeType(), $this->supportedAttributeTypes);
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

        foreach ($values as $assetCode) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $assetCode, static::class);
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($attribute->getReferenceDataName());
            $assetCode = AssetCode::fromString($assetCode);
            $assetExists = $this->assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);

            if (!$assetExists) {
                $message = sprintf(
                    'No asset "%s" for asset family "%s" has been found',
                    (string) $assetCode,
                    (string) $assetFamilyIdentifier
                );

                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->getCode(),
                    'code',
                    $message,
                    (string) $assetFamilyIdentifier,
                    $assetCode
                );
            }
        }
    }
}
