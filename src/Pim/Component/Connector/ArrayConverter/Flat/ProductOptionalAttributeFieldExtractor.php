<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;

/**
 * Extracts attribute field information
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductOptionalAttributeFieldExtractor
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

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
    public function getOptionalAttributeFields()
    {
        if (empty($this->optionalAttributeFields)) {
            $attributes = $this->attributeRepository->findAll();
            $currencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();
            $values = $this->valuesResolver->resolveEligibleValues($attributes);
            foreach ($values as $value) {
                if ($value['locale'] !== null && $value['scope'] !== null) {
                    $field = sprintf(
                        '%s-%s-%s',
                        $value['attribute'],
                        $value['locale'],
                        $value['scope']
                    );
                } elseif ($value['locale'] !== null) {
                    $field = sprintf(
                        '%s-%s',
                        $value['attribute'],
                        $value['locale']
                    );
                } elseif ($value['scope'] !== null) {
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
