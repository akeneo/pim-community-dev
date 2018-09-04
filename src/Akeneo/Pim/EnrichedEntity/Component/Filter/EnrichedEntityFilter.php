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

namespace Akeneo\Pim\EnrichedEntity\Component\Filter;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityExistsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Proposal reference data filter for an Elasticsearch query
 */
class EnrichedEntityFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var RecordExistsInterface */
    private $recordExists;

    /** @var ConfigurationRegistryInterface */
    private $enrichedEntityExists;

    /**
     * @param AttributeValidatorHelper         $attrValidatorHelper
     * @param RecordExistsInterface           $recordExists
     * @param EnrichedEntityExistsInterface   $enrichedEntityExists
     * @param array                           $supportedAttributeTypes
     * @param array                           $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        RecordExistsInterface $recordExists,
        EnrichedEntityExistsInterface $enrichedEntityExists,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->recordExists = $recordExists;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
        $this->enrichedEntityExists = $enrichedEntityExists;
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
        $enrichedEntityIdentifier = $attribute->getReferenceDataName();

        $enrichedEntityExists = null !== $enrichedEntityIdentifier &&
            !empty($enrichedEntityIdentifier) &&
            null !== $this->enrichedEntityExists->withIdentifier(EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier));

        return $enrichedEntityExists;
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

        foreach ($values as $recordCode) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $recordCode, static::class);
            $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($attribute->getReferenceDataName());
            $recordCode = RecordCode::fromString($recordCode);
            $recordExists = $this->recordExists->withEnrichedEntityAndCode($enrichedEntityIdentifier, $recordCode);

            if (!$recordExists) {
                $message = sprintf(
                    'No record "%s" for enriched entity "%s" has been found',
                    (string) $recordCode,
                    (string) $enrichedEntityIdentifier
                );

                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->getCode(),
                    'code',
                    $message,
                    (string) $enrichedEntityIdentifier,
                    $recordCode
                );
            }
        }
    }
}
