<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\AttributeColumnsResolverInterface;
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
class AttributeColumnsResolver implements AttributeColumnsResolverInterface
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

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AttributeValuesResolverInterface $valuesResolver
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->valuesResolver = $valuesResolver;
    }

    public function resolveIdentifierField(): string
    {
        if (empty($this->identifierField)) {
            $this->identifierField = $this->attributeRepository->getIdentifierCode();
        }

        return $this->identifierField;
    }

    public function resolveAttributeColumns(): array
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

    protected function resolveAttributeField(array $value, array $currencyCodes): array
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
     */
    public function resolveFlatAttributeName(string $attributeCode, ?string $localeCode, ?string $scopeCode): string
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
