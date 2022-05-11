<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\StandardFormat;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertStandardFormatIntoUserIntentsHandler
{
    public function __construct(private AttributeRepositoryInterface $attributeRepository)
    {
    }

    /**
     * @return UserIntent[]
     */
    public function __invoke(GetUserIntentsFromStandardFormat $getUserIntentsFromStandardFormat): array
    {
        $data = $getUserIntentsFromStandardFormat->standardFormat();
        return $this->getUserIntentsFromData($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return UserIntent[]
     */
    private function getUserIntentsFromData(array $data): array
    {
        $userIntents = [];

        if (\array_key_exists('family', $data) && null !== $data['family']) {
            $userIntents[] = new SetFamily($data['family']);
        }
        if (\array_key_exists('categories', $data) && null !== $data['categories']) {
            $userIntents[] = new SetCategories($data['categories']);
        }
        if (\array_key_exists('enabled', $data) && null !== $data['enabled']) {
            $userIntents[] = new SetEnabled($data['enabled']);
        }
        if (\array_key_exists('groups', $data) && null !== $data['groups']) {
            $userIntents[] = new SetGroups($data['groups']);
        }
        if (\array_key_exists('parent', $data) && null !== $data['parent']) {
            $userIntents[] = new ChangeParent($data['parent']);
        }

        if (\array_key_exists('values', $data)) {
            $codes = \array_keys($data['values']);
            $attributes = $this->attributeRepository->getAttributeTypeByCodes($codes);

            $dataValues = $data['values'];

            foreach ($attributes as $attributeCode => $attributeType) {
                $values = $dataValues[$attributeCode];
                foreach ($values as $value) {
                    $scope = $value['scope'];
                    $locale = $value['locale'];
                    $attributeData = $value['data'];
                    if ($this->isDataEmpty($attributeData, $attributeType)) {
                        $userIntents[] = new ClearValue($attributeCode, $scope, $locale);
                        continue;
                    }
                    $userIntents[] = match ($attributeType) {
                        AttributeTypes::BOOLEAN => new SetBooleanValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::DATE => new SetDateValue($attributeCode, $scope, $locale, new \DateTime($attributeData)),
                        AttributeTypes::FILE => new SetFileValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::IDENTIFIER => new SetIdentifierValue($attributeCode, $attributeData),
                        AttributeTypes::IMAGE => new SetImageValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::METRIC => new SetMeasurementValue($attributeCode, $scope, $locale, $attributeData['amount'], $attributeData['unit']),
                        AttributeTypes::NUMBER => new SetNumberValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::OPTION_SIMPLE_SELECT => new SetSimpleSelectValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::OPTION_MULTI_SELECT => new SetMultiSelectValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::PRICE_COLLECTION => new SetPriceCollectionValue(
                            $attributeCode,
                            $scope,
                            $locale,
                            \array_map(fn ($measurement) => new PriceValue($measurement['amount'], $measurement['currency']), $attributeData)
                        ),
                        AttributeTypes::TEXTAREA => new SetTextareaValue($attributeCode, $scope, $locale, $attributeData),
                        // TODO: use SetTableValue
                        AttributeTypes::TEXT, AttributeTypes::TABLE => new SetTextValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT => new SetSimpleReferenceEntityValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::REFERENCE_ENTITY_COLLECTION => new SetMultiReferenceEntityValue($attributeCode, $scope, $locale, $attributeData),
                        AttributeTypes::ASSET_COLLECTION => new SetAssetValue($attributeCode, $scope, $locale, $attributeData),
                        default => throw new \LogicException('Attribute type has no user intent associated')
                    };
                }
            }
        }

        if (\array_key_exists('associations', $data)) {
            foreach ($data['associations'] as $associationType => $values) {
                if (\array_key_exists('products', $values)) {
                    $userIntents[] = new ReplaceAssociatedProducts($associationType, $values['products']);
                }
                if (\array_key_exists('product_models', $values)) {
                    $userIntents[] = new ReplaceAssociatedProductModels($associationType, $values['product_models']);
                }
                if (\array_key_exists('groups', $values)) {
                    $userIntents[] = new ReplaceAssociatedGroups($associationType, $values['groups']);
                }
            }
        }

        if (\array_key_exists('quantified_associations', $data)) {
            foreach ($data['quantified_associations'] as $associationType => $values) {
                if (\array_key_exists('products', $values)) {
                    Assert::isArray($values['products']);
                    $userIntents[] = new ReplaceAssociatedQuantifiedProducts($associationType, \array_map(
                        fn ($quantifiedAssociation) => new QuantifiedEntity($quantifiedAssociation['identifier'], $quantifiedAssociation['quantity']),
                        $values['products']
                    ));
                }

                if (\array_key_exists('product_models', $values)) {
                    $userIntents[] = new ReplaceAssociatedQuantifiedProductModels($associationType, \array_map(
                        fn ($quantifiedAssociation) => new QuantifiedEntity($quantifiedAssociation['identifier'], $quantifiedAssociation['quantity']),
                        $values['product_models']
                    ));
                }
            }
        }

        return $userIntents;
    }

    private function isDataEmpty(mixed $data, string $attributeType): bool
    {
        if (null === $data
            || [] === $data
            || '' === $data
            || [''] === $data
            || [null] === $data) {
            return true;
        }

        if ($attributeType === AttributeTypes::METRIC && (null === $data['amount']
        || [] === $data['amount']
        || '' === $data['amount']
        || [''] === $data['amount']
        || [null] === $data['amount'])) {
            return true;
        }

        return false;
    }
}
