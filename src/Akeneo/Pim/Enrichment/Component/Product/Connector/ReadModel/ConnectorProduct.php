<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * This read model is dedicated to export product data for the connector, such as the API.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProduct
{
    public function __construct(
        private UuidInterface $uuid,
        private ?string $identifier,
        private \DateTimeImmutable $createdDate,
        private \DateTimeImmutable $updatedDate,
        private bool $enabled,
        private ?string $familyCode,
        private array $categoryCodes,
        private array $groupCodes,
        private ?string $parentProductModelCode,
        private array $associations,
        private array $quantifiedAssociations,
        // medata are for the status of the product in enterprise edition
        private array $metadata,
        private ReadValueCollection $values,
        private ?QualityScoreCollection $qualityScores,
        private ?ProductCompletenessCollection $completenesses
    ) {
        try {
            $this->validateAssociationsFormat();
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Malformed associations parameter: %s',
                    \json_encode($this->associations)
                ),
                0,
                $e
            );
        }

        try {
            $this->validateQuantifiedAssociationsFormat();
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Malformed quantified associations parameter %s',
                    \json_encode($this->quantifiedAssociations)
                ),
                0,
                $e
            );
        }
    }

    private function validateAssociationsFormat(): void
    {
        Assert::allIsMap($this->associations);
        foreach ($this->associations as $associationsByType) {
            Assert::allRegex(array_keys($associationsByType), '/products|product_models|groups/');
            Assert::isMap($associationsByType);
            if (array_key_exists('products', $associationsByType)) {
                Assert::isArray($associationsByType['products']);
                foreach ($associationsByType['products'] as $associatedProduct) {
                    Assert::isMap($associatedProduct);
                    Assert::keyExists($associatedProduct, 'uuid');
                    Assert::stringNotEmpty($associatedProduct['uuid']);
                    Assert::true(Uuid::isValid($associatedProduct['uuid']), sprintf('The associated product "%s" is not a valid uuid', $associatedProduct['uuid']));
                    Assert::keyExists($associatedProduct, 'identifier');
                    Assert::nullOrString($associatedProduct['identifier']);
                }
            }
            if (array_key_exists('product_models', $associationsByType)) {
                Assert::isArray($associationsByType['product_models']);
                Assert::allStringNotEmpty($associationsByType['product_models']);
            }
            if (array_key_exists('groups', $associationsByType)) {
                Assert::isArray($associationsByType['groups']);
                Assert::allStringNotEmpty($associationsByType['groups']);
            }
        }
    }

    private function validateQuantifiedAssociationsFormat(): void
    {
        Assert::allIsMap($this->quantifiedAssociations);
        foreach ($this->quantifiedAssociations as $quantifiedAssociationsByType) {
            Assert::allRegex(array_keys($quantifiedAssociationsByType), '/products|product_models/');
            Assert::isMap($quantifiedAssociationsByType);
            if (array_key_exists('products', $quantifiedAssociationsByType)) {
                Assert::isArray($quantifiedAssociationsByType['products']);
                foreach ($quantifiedAssociationsByType['products'] as $quantifiedAssociatedProduct) {
                    Assert::isMap($quantifiedAssociatedProduct);
                    Assert::keyExists($quantifiedAssociatedProduct, 'uuid');
                    Assert::stringNotEmpty($quantifiedAssociatedProduct['uuid']);
                    Assert::true(Uuid::isValid($quantifiedAssociatedProduct['uuid']), sprintf('The associated product "%s" is not a valid uuid', $quantifiedAssociatedProduct['uuid']));
                    Assert::keyExists($quantifiedAssociatedProduct, 'identifier');
                    Assert::nullOrString($quantifiedAssociatedProduct['identifier']);
                    Assert::keyExists($quantifiedAssociatedProduct, 'quantity');
                }
            }
            if (array_key_exists('product_models', $quantifiedAssociationsByType)) {
                Assert::isArray($quantifiedAssociationsByType['product_models']);
                foreach ($quantifiedAssociationsByType['product_models'] as $quantifiedAssociatedProductModel) {
                    Assert::isMap($quantifiedAssociatedProductModel);
                    Assert::keyExists($quantifiedAssociatedProductModel, 'identifier');
                    Assert::nullOrString($quantifiedAssociatedProductModel['identifier']);
                    Assert::keyExists($quantifiedAssociatedProductModel, 'quantity');
                }
            }
        }
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function identifier(): ?string
    {
        return $this->identifier;
    }

    public function createdDate(): \DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function updatedDate(): \DateTimeImmutable
    {
        return $this->updatedDate;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function familyCode(): ?string
    {
        return $this->familyCode;
    }

    public function categoryCodes(): array
    {
        return $this->categoryCodes;
    }

    public function groupCodes(): array
    {
        return $this->groupCodes;
    }

    public function parentProductModelCode(): ?string
    {
        return $this->parentProductModelCode;
    }

    public function associations(): array
    {
        return $this->associations;
    }

    public function quantifiedAssociations(): array
    {
        return $this->quantifiedAssociations;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function values(): ReadValueCollection
    {
        return $this->values;
    }

    public function attributeCodesInValues(): array
    {
        return $this->values->getAttributeCodes();
    }

    public function qualityScores(): ?QualityScoreCollection
    {
        return $this->qualityScores;
    }

    public function completenesses(): ?ProductCompletenessCollection
    {
        return $this->completenesses;
    }

    /**
     * The value cannot be an object.
     *
     * @param string|string[] $value
     */
    public function addMetadata(string $key, $value): ConnectorProduct
    {
        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $this->quantifiedAssociations,
            array_merge($this->metadata, [$key => $value]),
            $this->values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    /**
     * @param array $optionLabels array of all the labels of options, indexed by option code
     *                            ['option_code' => ['en_US' => 'translation']
     */
    public function buildLinkedData(array $optionLabels): ConnectorProduct
    {
        $values = $this->values->map(function (ValueInterface $value) use ($optionLabels) {
            $optionCodes = \array_keys($optionLabels[$value->getAttributeCode()] ?? []);
            if ($value instanceof OptionValue) {
                $index = \array_search(\strtolower($value->getData()), \array_map('strtolower', $optionCodes), true);
                $optionCodeWithRightCase = false !== $index ? $optionCodes[$index] : $value->getData();

                return new OptionValueWithLinkedData(
                    $value->getAttributeCode(),
                    $value->getData(),
                    $value->getScopeCode(),
                    $value->getLocaleCode(),
                    [
                        'attribute' => $value->getAttributeCode(),
                        'code' => (string)$optionCodeWithRightCase,
                        'labels' => $optionLabels[$value->getAttributeCode()][$optionCodeWithRightCase] ?? []
                    ],
                );
            } elseif ($value instanceof OptionsValue) {
                $linkedData = [];
                foreach ($value->getData() as $optionCode) {
                    $index = \array_search(\strtolower($optionCode), \array_map('strtolower', $optionCodes), true);
                    $optionCodeWithRightCase = false !== $index ? $optionCodes[$index] : $optionCode;

                    $linkedData[$optionCode] = [
                        'attribute' => $value->getAttributeCode(),
                        'code' => (string)$optionCodeWithRightCase,
                        'labels' => $optionLabels[$value->getAttributeCode()][$optionCodeWithRightCase] ?? [],
                    ];
                }

                return new OptionsValueWithLinkedData(
                    $value->getAttributeCode(),
                    $value->getData(),
                    $value->getScopeCode(),
                    $value->getLocaleCode(),
                    $linkedData
                );
            } else {
                return $value;
            }
        });

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $this->quantifiedAssociations,
            $this->metadata,
            $values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function buildWithQualityScores(QualityScoreCollection $productQualityScores): self
    {
        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $this->quantifiedAssociations,
            $this->metadata,
            $this->values,
            $productQualityScores,
            $this->completenesses
        );
    }

    public function filterValuesByAttributeCodesAndLocaleCodes(array $attributeCodesToKeep, array $localeCodesToKeep): ConnectorProduct
    {
        $attributeCodes = array_flip($attributeCodesToKeep);
        $localeCodes = array_flip($localeCodesToKeep);

        $values = $this->values->filter(function (ValueInterface $value) use ($attributeCodes, $localeCodes) {
            return isset($attributeCodes[$value->getAttributeCode()])
                && (!$value->isLocalizable() || isset($localeCodes[$value->getLocaleCode()]));
        });

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $this->quantifiedAssociations,
            $this->metadata,
            $values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function associatedProductUuids(): array
    {
        $associatedProducts = [];
        foreach ($this->associations as $associationType => $associations) {
            $associatedProducts[] = array_map(
                fn (array $associatedProduct): string => $associatedProduct['uuid'],
                $associations['products']
            );
        }

        return !empty($associatedProducts) ? array_unique(array_merge(...$associatedProducts)) : [];
    }

    public function associatedProductModelCodes(): array
    {
        $associatedProductModels = [];
        foreach ($this->associations as $associations) {
            $associatedProductModels[] = $associations['product_models'];
        }

        return !empty($associatedProductModels) ? array_unique(array_merge(...$associatedProductModels)) : [];
    }

    public function associatedWithQuantityProductIdentifiers()
    {
        $associatedWithQuantityProducts = array_map(function ($quantifiedAssociations) {
            return array_column($quantifiedAssociations['products'], 'identifier');
        }, array_values($this->quantifiedAssociations));

        if (empty($associatedWithQuantityProducts)) {
            return [];
        }

        return array_values(array_unique(array_merge(...$associatedWithQuantityProducts)));
    }

    public function associatedWithQuantityProductModelCodes()
    {
        $associatedWithQuantityProductModels = array_map(function ($quantifiedAssociations) {
            return array_column($quantifiedAssociations['product_models'], 'identifier');
        }, array_values($this->quantifiedAssociations));

        if (empty($associatedWithQuantityProductModels)) {
            return [];
        }

        return array_values(array_unique(array_merge(...$associatedWithQuantityProductModels)));
    }

    public function filterAssociatedProductModelsByProductModelCodes(array $productModelCodesToFilter): ConnectorProduct
    {
        $filteredAssociations = [];
        foreach ($this->associations as $associationType => $association) {
            $filteredAssociations[$associationType]['products'] = $association['products'];
            $filteredAssociations[$associationType]['product_models'] = array_values(array_intersect(
                $association['product_models'],
                $productModelCodesToFilter
            ));
            $filteredAssociations[$associationType]['groups'] = $association['groups'];
        }

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $filteredAssociations,
            $this->quantifiedAssociations,
            $this->metadata,
            $this->values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function filterAssociatedWithQuantityProductModelsByProductModelCodes(array $productModelCodesToFilter): ConnectorProduct
    {
        $filteredQuantifiedAssociations = [];
        foreach ($this->quantifiedAssociations as $associationType => $quantifiedAssociation) {
            $filteredProductModelQuantifiedAssociations = array_filter(
                $quantifiedAssociation['product_models'],
                function ($quantifiedLink) use ($productModelCodesToFilter) {
                    return in_array($quantifiedLink['identifier'], $productModelCodesToFilter);
                }
            );

            $filteredQuantifiedAssociations[$associationType]['products'] = $quantifiedAssociation['products'];
            $filteredQuantifiedAssociations[$associationType]['product_models'] = array_values($filteredProductModelQuantifiedAssociations);
        }

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $filteredQuantifiedAssociations,
            $this->metadata,
            $this->values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function filterAssociatedWithQuantityProductsByProductIdentifiers(array $productIdentifiersToFilter): ConnectorProduct
    {
        $filteredQuantifiedAssociations = [];
        foreach ($this->quantifiedAssociations as $associationType => $quantifiedAssociation) {
            $filteredProductQuantifiedAssociations = array_filter(
                $quantifiedAssociation['products'],
                function ($quantifiedLink) use ($productIdentifiersToFilter) {
                    return in_array($quantifiedLink['identifier'], $productIdentifiersToFilter);
                }
            );

            $filteredQuantifiedAssociations[$associationType]['products'] = array_values($filteredProductQuantifiedAssociations);
            $filteredQuantifiedAssociations[$associationType]['product_models'] = $quantifiedAssociation['product_models'];
        }

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $filteredQuantifiedAssociations,
            $this->metadata,
            $this->values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function filterAssociatedProductsByProductIdentifiers(array $productIdentifiersToFilter): ConnectorProduct
    {
        $filteredAssociations = [];
        foreach ($this->associations as $associationType => $association) {
            $filteredAssociations[$associationType]['products'] = array_values(array_filter(
                $association['products'],
                fn (array $associatedProduct): bool => in_array($associatedProduct['identifier'], $productIdentifiersToFilter)
            ));
            $filteredAssociations[$associationType]['product_models'] = $association['product_models'];
            $filteredAssociations[$associationType]['groups'] = $association['groups'];
        }

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $filteredAssociations,
            $this->quantifiedAssociations,
            $this->metadata,
            $this->values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function filterAssociatedProductsByProductUuids(array $productUuidsToFilter): ConnectorProduct
    {
        $filteredAssociations = [];
        foreach ($this->associations as $associationType => $association) {
            $filteredAssociations[$associationType]['products'] = array_values(array_filter(
                $association['products'],
                fn (array $associatedProduct): bool => in_array($associatedProduct['uuid'], $productUuidsToFilter)
            ));
            $filteredAssociations[$associationType]['product_models'] = $association['product_models'];
            $filteredAssociations[$associationType]['groups'] = $association['groups'];
        }

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $filteredAssociations,
            $this->quantifiedAssociations,
            $this->metadata,
            $this->values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function filterByCategoryCodes(array $categoryCodesToFilter): ConnectorProduct
    {
        $categoryCodes = array_values(array_intersect($this->categoryCodes, $categoryCodesToFilter));

        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $this->quantifiedAssociations,
            $this->metadata,
            $this->values,
            $this->qualityScores,
            $this->completenesses
        );
    }

    public function buildWithCompletenesses(ProductCompletenessCollection $productCompletenessCollection): ConnectorProduct
    {
        return new self(
            $this->uuid,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->enabled,
            $this->familyCode,
            $this->categoryCodes,
            $this->groupCodes,
            $this->parentProductModelCode,
            $this->associations,
            $this->quantifiedAssociations,
            $this->metadata,
            $this->values,
            $this->qualityScores,
            $productCompletenessCollection
        );
    }
}
