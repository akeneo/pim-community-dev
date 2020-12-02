<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;

/**
 * This read model is dedicated to export product data for the connector, such as the API.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProduct
{
    /** @var int */
    private $id;

    /** @var string */
    private $identifier;

    /** @var \DateTimeImmutable */
    private $createdDate;

    /** @var \DateTimeImmutable */
    private $updatedDate;

    /** @var bool */
    private $enabled;

    /** @var null|string */
    private $familyCode;

    /** @var array */
    private $categoryCodes;

    /** @var array */
    private $groupCodes;

    /** @var null|string */
    private $parentProductModelCode;

    /** @var array */
    private $associations;

    /** @var array */
    private $quantifiedAssociations;

    /** @var array medata are for the status of the product in enterprise edition */
    private $metadata;

    /** @var ReadValueCollection */
    private $values;

    private ?ChannelLocaleRateCollection $qualityScores;

    public function __construct(
        int $id,
        string $identifier,
        \DateTimeImmutable $createdDate,
        \DateTimeImmutable $updatedDate,
        bool $enabled,
        ?string $familyCode,
        array $categoryCodes,
        array $groups,
        ?string $parentProductModelCode,
        array $associations,
        array $quantifiedAssociations,
        array $metadata,
        ReadValueCollection $values,
        ?ChannelLocaleRateCollection $qualityScores
    ) {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->enabled = $enabled;
        $this->familyCode = $familyCode;
        $this->categoryCodes = $categoryCodes;
        $this->groupCodes = $groups;
        $this->parentProductModelCode = $parentProductModelCode;
        $this->values = $values;
        $this->associations = $associations;
        $this->quantifiedAssociations = $quantifiedAssociations;
        $this->metadata = $metadata;
        $this->qualityScores = $qualityScores;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function identifier(): string
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

    public function qualityScores(): ?ChannelLocaleRateCollection
    {
        return $this->qualityScores;
    }

    /**
     * The value cannot be an object.
     *
     * @param string|string[] $value
     */
    public function addMetadata(string $key, $value): ConnectorProduct
    {
        return new self(
            $this->id,
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
            $this->qualityScores
        );
    }

    /**
     * @param array $optionLabels array of all the labels of options, indexed by option code
     *                            ['option_code' => ['en_US' => 'translation']
     */
    public function buildLinkedData(array $optionLabels): ConnectorProduct
    {
        $values = $this->values->map(function (ValueInterface $value) use ($optionLabels) {
            if ($value instanceof OptionValue) {
                return new OptionValueWithLinkedData(
                    $value->getAttributeCode(),
                    $value->getData(),
                    $value->getScopeCode(),
                    $value->getLocaleCode(),
                    [
                        "attribute" => $value->getAttributeCode(),
                        "code"=> $value->getData(),
                        "labels" => $optionLabels[$value->getAttributeCode()][$value->getData()] ?? []
                    ],
                );
            } elseif ($value instanceof OptionsValue) {
                $linkedData = [];
                foreach ($value->getData() as $optionCode) {
                    $linkedData[$optionCode] = [
                        "attribute" => $value->getAttributeCode(),
                        "code"=> $optionCode,
                        "labels" => $optionLabels[$value->getAttributeCode()][$optionCode] ?? [],
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
            $this->id,
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
            $this->qualityScores
        );
    }

    public function buildWithQualityScores(ChannelLocaleRateCollection $productQualityScores): self
    {
        return new self(
            $this->id,
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
            $this->id,
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
            $this->qualityScores
        );
    }

    public function associatedProductIdentifiers(): array
    {
        $associatedProducts = [];
        foreach ($this->associations as $associationType => $associations) {
            $associatedProducts[] = $associations['products'];
        }

        return !empty($associatedProducts) ? array_unique(array_merge(...$associatedProducts)) : [];
    }

    public function associatedProductModelCodes(): array
    {
        $associatedProductModels = [];
        foreach ($this->associations as $associationType => $associations) {
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
            $this->id,
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
            $this->qualityScores
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
            $this->id,
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
            $this->qualityScores
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
            $this->id,
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
            $this->qualityScores
        );
    }

    public function filterAssociatedProductsByProductIdentifiers(array $productIdentifiersToFilter): ConnectorProduct
    {
        $filteredAssociations = [];
        foreach ($this->associations as $associationType => $association) {
            $filteredAssociations[$associationType]['products'] = array_values(array_intersect(
                $association['products'],
                $productIdentifiersToFilter
            ));
            $filteredAssociations[$associationType]['product_models'] = $association['product_models'];
            $filteredAssociations[$associationType]['groups'] = $association['groups'];
        }

        return new self(
            $this->id,
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
            $this->qualityScores
        );
    }

    public function filterByCategoryCodes(array $categoryCodesToFilter): ConnectorProduct
    {
        $categoryCodes =  array_values(array_intersect($this->categoryCodes, $categoryCodesToFilter));

        return new self(
            $this->id,
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
            $this->qualityScores
        );
    }
}
