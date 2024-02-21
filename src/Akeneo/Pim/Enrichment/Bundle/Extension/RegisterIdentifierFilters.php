<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Extension;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterIdentifierFilters
{
    public const PRODUCT_DATAGRID_NAME = 'product-grid';

    public function __construct(
        private GetAttributes $getAttributes,
        private GetAttributeTranslations $getAttributeTranslations,
        private UserContext $userContext,
        private RequestParameters $requestParams,
        private RequestStack $requestStack,
    ) {
    }

    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfiguration = $event->getConfig();

        $attributeIdentifiers = array_filter($this->getAttributes->forType(AttributeTypes::IDENTIFIER));
        $attributeCodes = array_keys($attributeIdentifiers);
        $attributeTranslations = $this->getAttributeTranslations->byAttributeCodesAndLocale($attributeCodes, $this->getCurrentLocaleCode());

        $filters = $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY);
        foreach ($attributeIdentifiers as $attributeCode => $attributeIdentifier) {
            $filters['columns'][$attributeCode] = $this->buildFilter($attributeIdentifier, $attributeTranslations);
        }

        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);
    }

    private function buildFilter(Attribute $attribute, array $attributeTranslations): array
    {
        return [
            'type' => 'product_value_string',
            'ftype' => 'identifier',
            'label' => $attributeTranslations[$attribute->code()] ?? sprintf('[%s]', $attribute->code()),
            'data_name' => $attribute->code(),
            'options' => [
                'field_options' => [
                    'attr' => [
                        'choice_list' => true,
                        'empty_choice' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    private function getCurrentLocaleCode(): string
    {
        $dataLocale = $this->requestParams->get('dataLocale', null);
        if ($dataLocale) {
            return $dataLocale;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->get('dataLocale')) {
            return $request->get('dataLocale');
        }

        $userCatalogLocale = $this->userContext->getUser()->getCatalogLocale();
        if ($userCatalogLocale) {
            return $userCatalogLocale->getCode();
        }

        return 'en_US';
    }
}
