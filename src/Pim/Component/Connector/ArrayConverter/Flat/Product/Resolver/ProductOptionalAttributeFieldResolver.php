<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver;

use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;

/**
 * Resolve attribute field information
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: missing spec!
 */
class ProductOptionalAttributeFieldResolver
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var array */
    protected $optionalAttributeFields;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /**
     * @param CurrencyRepositoryInterface  $currencyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeValuesResolver      $valuesResolver
     */
    public function __construct(
        CurrencyRepositoryInterface $currencyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeValuesResolver $valuesResolver
    ) {
        $this->currencyRepository  = $currencyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->valuesResolver      = $valuesResolver;
    }

    /**
     * @return array
     */
    public function resolveOptionalAttributeFields()
    {
        if (empty($this->optionalAttributeFields)) {
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

                if ('pim_catalog_price_collection' === $value['type']) {
                    $this->optionalAttributeFields[] = $field;
                    foreach ($currencyCodes as $currencyCode) {
                        $currencyField = sprintf('%s-%s', $field, $currencyCode);
                        $this->optionalAttributeFields[] = $currencyField;
                    }
                } elseif ('pim_catalog_metric' === $value['type']) {
                    $this->optionalAttributeFields[] = $field;
                    $metricField = sprintf('%s-%s', $field, 'unit');
                    $this->optionalAttributeFields[] = $metricField;
                } else {
                    $this->optionalAttributeFields[] = $field;
                }
            }
        }

        return $this->optionalAttributeFields;
    }
}
