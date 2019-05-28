<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

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

    /** @var array medata are for the status of the product in enterprise edition */
    private $metadata;

    /** @var ReadValueCollection */
    private $values;

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
        array $metadata,
        ReadValueCollection $values
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
        $this->metadata = $metadata;
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
            array_merge($this->metadata, [$key => $value]),
            $this->values
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
            $this->metadata,
            $values
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
            $this->metadata,
            $this->values
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
            $this->metadata,
            $this->values
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
            $this->metadata,
            $this->values
        );
    }
}
