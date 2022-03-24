<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductModel
{
    public function __construct(
        private int $id,
        private string $code,
        private \DateTimeInterface $createdDate,
        private \DateTimeInterface $updatedDate,
        private ?string $parentCode,
        private string $familyCode,
        private string $familyVariantCode,
        private array $metadata,
        private array $associations,
        private array $quantifiedAssociations,
        private array $categoryCodes,
        private ReadValueCollection $values,
        private ?QualityScoreCollection $qualityScores
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function createdDate(): \DateTimeInterface
    {
        return $this->createdDate;
    }

    public function updatedDate(): \DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function parentCode(): ?string
    {
        return $this->parentCode;
    }

    public function familyCode(): string
    {
        return $this->familyCode;
    }

    public function familyVariantCode(): string
    {
        return $this->familyVariantCode;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function associations(): array
    {
        return $this->associations;
    }

    public function quantifiedAssociations(): array
    {
        return $this->quantifiedAssociations;
    }

    public function categoryCodes(): array
    {
        return $this->categoryCodes;
    }

    public function values(): ReadValueCollection
    {
        return $this->values;
    }

    public function qualityScores(): ?QualityScoreCollection
    {
        return $this->qualityScores;
    }

    public function attributeCodesInValues(): array
    {
        return $this->values->getAttributeCodes();
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

    public function filterByCategoryCodes(array $categoryCodesToKeep): ConnectorProductModel
    {
        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            $this->metadata,
            $this->associations,
            $this->quantifiedAssociations,
            array_values(array_intersect($this->categoryCodes, $categoryCodesToKeep)),
            $this->values,
            $this->qualityScores
        );
    }

    public function filterValuesByAttributeCodesAndLocaleCodes(
        array $attributeCodesToKeep,
        array $localeCodesToKeep
    ): ConnectorProductModel {
        $attributeCodes = array_flip($attributeCodesToKeep);
        $localeCodes = array_flip($localeCodesToKeep);
        $values = $this->values->filter(
            function (ValueInterface $value) use ($attributeCodes, $localeCodes) {
                return isset($attributeCodes[$value->getAttributeCode()])
                    && (!$value->isLocalizable() || isset($localeCodes[$value->getLocaleCode()]));
            }
        );

        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            $this->metadata,
            $this->associations,
            $this->quantifiedAssociations,
            $this->categoryCodes,
            $values,
            $this->qualityScores
        );
    }

    public function filterAssociatedProductsByProductIdentifiers(
        array $productIdentifiersToFilter
    ): ConnectorProductModel {
        $filteredAssociations = [];
        foreach ($this->associations as $associationType => $association) {
            $filteredAssociations[$associationType]['products'] = array_values(
                array_intersect(
                    $association['products'],
                    $productIdentifiersToFilter
                )
            );
            $filteredAssociations[$associationType]['product_models'] = $association['product_models'];
            $filteredAssociations[$associationType]['groups'] = $association['groups'];
        }

        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            $this->metadata,
            $filteredAssociations,
            $this->quantifiedAssociations,
            $this->categoryCodes,
            $this->values,
            $this->qualityScores
        );
    }

    public function filterAssociatedProductModelsByProductModelCodes(
        array $productModelCodesToFilter
    ): ConnectorProductModel {
        $filteredAssociations = [];
        foreach ($this->associations as $associationType => $association) {
            $filteredAssociations[$associationType]['products'] = $association['products'];
            $filteredAssociations[$associationType]['product_models'] = array_values(
                array_intersect(
                    $association['product_models'],
                    $productModelCodesToFilter
                )
            );
            $filteredAssociations[$associationType]['groups'] = $association['groups'];
        }

        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            $this->metadata,
            $filteredAssociations,
            $this->quantifiedAssociations,
            $this->categoryCodes,
            $this->values,
            $this->qualityScores
        );
    }


    public function filterAssociatedWithQuantityProductModelsByProductModelCodes(array $productModelCodesToFilter): ConnectorProductModel
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
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            $this->metadata,
            $this->associations,
            $filteredQuantifiedAssociations,
            $this->categoryCodes,
            $this->values,
            $this->qualityScores
        );
    }

    public function filterAssociatedWithQuantityProductsByProductIdentifiers(array $productIdentifiersToFilter): ConnectorProductModel
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
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            $this->metadata,
            $this->associations,
            $filteredQuantifiedAssociations,
            $this->categoryCodes,
            $this->values,
            $this->qualityScores
        );
    }

    public function addMetadata(string $key, $value): ConnectorProductModel
    {
        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            array_merge($this->metadata, [$key => $value]),
            $this->associations,
            $this->quantifiedAssociations,
            $this->categoryCodes,
            $this->values,
            $this->qualityScores
        );
    }

    public function buildWithQualityScores(?QualityScoreCollection $productQualityScores): ConnectorProductModel
    {
        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->parentCode,
            $this->familyCode,
            $this->familyVariantCode,
            $this->metadata,
            $this->associations,
            $this->quantifiedAssociations,
            $this->categoryCodes,
            $this->values,
            $productQualityScores
        );
    }
}
