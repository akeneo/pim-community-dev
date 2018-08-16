<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * Resolve attribute field information
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeColumnsResolver
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AttributeValuesResolverInterface */
    protected $valuesResolver;

    /** @var array */
    protected $attributesFields = [];

    /** @var string */
    protected $identifierField;

    /**
     * @param AttributeRepositoryInterface     $attributeRepository
     * @param CurrencyRepositoryInterface      $currencyRepository
     * @param AttributeValuesResolverInterface $valuesResolver
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AttributeValuesResolverInterface $valuesResolver
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->valuesResolver = $valuesResolver;
    }

    /**
     * @return string
     */
    public function resolveIdentifierField()
    {
        if (empty($this->identifierField)) {
            $this->identifierField = $this->attributeRepository->getIdentifierCode();
        }

        return $this->identifierField;
    }

    /**
     * @return array
     */
    public function resolveAttributeColumns()
    {
        if (empty($this->attributesFields)) {
            // TODO: Put a Cursor to avoid a findAll on attributes (╯°□°)╯︵ ┻━┻
            $attributes = $this->attributeRepository->findAll();
            $currencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();
            $values = $this->valuesResolver->resolveEligibleValues($attributes);
            foreach ($values as $value) {
                $fields = $this->resolveAttributeField($value, $currencyCodes);
                foreach ($fields as $field) {
                    $this->attributesFields[] = $field;
                }
            }
        }

        return $this->attributesFields;
    }

    /**
     * Resolves the attribute field name
     *
     * @param array $value
     * @param array $currencyCodes
     *
     * @return array
     */
    protected function resolveAttributeField(array $value, array $currencyCodes)
    {
        $field = $this->resolveFlatAttributeName($value['attribute'], $value['locale'], $value['scope']);

        if (AttributeTypes::PRICE_COLLECTION === $value['type']) {
            $fields[] = $field;
            foreach ($currencyCodes as $currencyCode) {
                $currencyField = sprintf('%s-%s', $field, $currencyCode);
                $fields[] = $currencyField;
            }
        } elseif (AttributeTypes::METRIC === $value['type']) {
            $fields[] = $field;
            $metricField = sprintf('%s-%s', $field, 'unit');
            $fields[] = $metricField;
        } else {
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Resolve the full flat attribute name depending on the $attributeCode, the $localeCode and the $scopeCode.
     *
     * Examples:
     *
     *  description-en_US-mobile
     *  name-ecommerce
     *  weight
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     *
     * @return string
     */
    public function resolveFlatAttributeName($attributeCode, $localeCode, $scopeCode)
    {
        $field = $attributeCode;

        if (null !== $localeCode) {
            $field = sprintf('%s-%s', $field, $localeCode);
        }

        if (null !== $scopeCode) {
            $field = sprintf('%s-%s', $field, $scopeCode);
        }

        return $field;
    }
}
