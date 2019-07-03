<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

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
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var \DateTimeInterface */
    private $createdDate;

    /** @var \DateTimeInterface */
    private $updatedDate;

    /** @var null|string */
    private $parentCode;

    /** @var string */
    private $familyCode;

    /** @var string */
    private $familyVariantCode;

    /** @var array */
    private $metadata;

    /** @var array */
    private $associations;

    /** @var array */
    private $categoryCodes;

    /** @var ReadValueCollection */
    private $values;

    public function __construct(
        int $id,
        string $code,
        \DateTimeInterface $createdDate,
        \DateTimeInterface $updatedDate,
        ?string $parentCode,
        string $familyCode,
        string $familyVariantCode,
        array $metadata,
        array $associations,
        array $categoryCodes,
        ReadValueCollection $values
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->parentCode = $parentCode;
        $this->familyCode = $familyCode;
        $this->familyVariantCode = $familyVariantCode;
        $this->metadata = $metadata;
        $this->associations = $associations;
        $this->categoryCodes = $categoryCodes;
        $this->values = $values;
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

    public function categoryCodes(): array
    {
        return $this->categoryCodes;
    }

    public function values(): ReadValueCollection
    {
        return $this->values;
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
            array_values(array_intersect($this->categoryCodes, $categoryCodesToKeep)),
            $this->values
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
            $this->categoryCodes,
            $values
        );
    }

    public function filterAssociatedProductsByProductIdentifiers(array $productIdentifiersToFilter
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
            $this->categoryCodes,
            $this->values
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
            $this->categoryCodes,
            $this->values
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
            $this->categoryCodes,
            $this->values
        );
    }
}
