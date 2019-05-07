<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
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

    /** @var ValueCollectionInterface */
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
        ValueCollectionInterface $values
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

    public function values(): ValueCollectionInterface
    {
        return $this->values;
    }

    public static function fromProductWriteModel(ProductInterface $product, ValueCollectionInterface $values, array $metadata = []): ConnectorProduct
    {
        return new self(
            $product->getId(),
            $product->getIdentifier(),
            \DateTimeImmutable::createFromMutable($product->getCreated()),
            \DateTimeImmutable::createFromMutable($product->getUpdated()),
            $product->isEnabled(),
            $product->getFamily() !== null ? $product->getFamily()->getCode() : null,
            $product->getCategoryCodes(),
            $product->getGroupCodes(),
            $product->isVariant() ? $product->getParent()->getCode() : null,
            self::productAssociationsAsArray($product),
            $metadata,
            $values
        );
    }

    public static function productAssociationsAsArray(ProductInterface $product): array
    {
        $associations = [];
        foreach ($product->getAllAssociations() as $association) {
            $associations[$association->getAssociationType()->getCode()] = [
                'products' => array_map(function (ProductInterface $product) {
                    return $product->getIdentifier();
                }, $association->getProducts()->toArray()),
                'product_models' => array_map(function (ProductModelInterface $productModel) {
                    return $productModel->getCode();
                }, $association->getProductModels()->toArray()),
                'groups' => array_map(function (GroupInterface $group) {
                    return $group->getCode();
                }, $association->getGroups()->toArray())
            ];
        }

        return $associations;
    }

    /**
     * Returns the list of category codes of this product belonging the the set in parameter
     *
     * @param string[] $categoryCodesToFilter
     *
     * @return string[]
     */
    public function filteredCategoryCodes(array $categoryCodesToFilter): array
    {
        return array_intersect($this->categoryCodes, $categoryCodesToFilter);
    }

    /**
     * Returns the list of attribute codes used in values of this product
     *
     * @return string[]
     */
    public function attributeCodes(): array
    {
        return array_unique(
            array_map(function (ValueInterface $value) {
                return $value->getAttributeCode();
            }, $this->values->toArray())
        );
    }

    /**
     * Returns the value list with an attribute belonging to the set in parameter
     *
     * @param string[] $attributeCodesToFilter
     *
     * @return ValueCollection
     */
    public function valuesWithFilteredAttributeCodes(array $attributeCodesToFilter): ValueCollectionInterface
    {
        $values = new ValueCollection();
        foreach ($this->values as $value) {
            if (in_array($value->getAttributeCode(), $attributeCodesToFilter)) {
                $values->add($value);
            }
        }

        return $values;
    }

    /**
     * Returns the associated product codes
     *
     * @return string[]
     */
    public function associatedProducts(): array
    {
        $products = [];
        foreach ($this->associations as $associationType => $associations) {
            $products = array_merge($products, $associations['products']);
        }

        return array_unique($products);
    }

    /**
     * Returns the associations property, with only the product codes as parameter
     *
     * @param string[] $productCodesToFilter
     *
     * @return array
     */
    public function associationsWithFilteredProductCodes($productCodesToFilter)
    {
        $result = [];
        foreach ($this->associations as $associationType => $association) {
            $result[$associationType]['groups'] = $association['groups'];
            $result[$associationType]['product_models'] = $association['product_models'];
            $result[$associationType]['products'] = array_intersect(
                $association['products'],
                $productCodesToFilter
            );
        }

        return $result;
    }

}
