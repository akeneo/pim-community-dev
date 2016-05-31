<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;

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

    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /** @var array */
    protected $attributesFields;

    /** @var string */
    protected $identifierField;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CurrencyRepositoryInterface  $currencyRepository
     * @param AttributeValuesResolver      $valuesResolver
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AttributeValuesResolver $valuesResolver
    ) {
        $this->currencyRepository  = $currencyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->valuesResolver      = $valuesResolver;
    }

    /**
     * @return string
     */
    public function resolveIdentifierField()
    {
        if (empty($this->identifierField)) {
            $attribute = $this->attributeRepository->getIdentifier();
            $this->identifierField = $attribute->getCode();
        }

        return $this->identifierField;
    }

    /**
     * @return array
     */
    public function resolveAttributeColumns()
    {
        if (empty($this->attributesFields)) {
            $attributes = $this->attributeRepository->findAll();
            $currencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();
            $values = $this->valuesResolver->resolveEligibleValues($attributes);
            foreach ($values as $value) {
                if (null !== $value['locale'] && null !== $value['scope']) {
                    $field = sprintf(
                        '%s-%s-%s',
                        $value['attribute'],
                        $value['locale'],
                        $value['scope']
                    );
                } elseif (null !== $value['locale']) {
                    $field = sprintf(
                        '%s-%s',
                        $value['attribute'],
                        $value['locale']
                    );
                } elseif (null !== $value['scope']) {
                    $field = sprintf(
                        '%s-%s',
                        $value['attribute'],
                        $value['scope']
                    );
                } else {
                    $field = $value['attribute'];
                }

                if (AttributeTypes::PRICE_COLLECTION === $value['type']) {
                    $this->attributesFields[] = $field;
                    foreach ($currencyCodes as $currencyCode) {
                        $currencyField = sprintf('%s-%s', $field, $currencyCode);
                        $this->attributesFields[] = $currencyField;
                    }
                } elseif (AttributeTypes::METRIC === $value['type']) {
                    $this->attributesFields[] = $field;
                    $metricField = sprintf('%s-%s', $field, 'unit');
                    $this->attributesFields[] = $metricField;
                } else {
                    $this->attributesFields[] = $field;
                }
            }
        }

        return $this->attributesFields;
    }
}
