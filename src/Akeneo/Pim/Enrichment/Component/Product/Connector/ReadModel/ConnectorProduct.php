<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;

/**
 * This read model is dedicated to export product data for the connector, such as the API.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProduct
{
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

    public static function fromProductWriteModel(ProductInterface $product)
    {
        return new self(
            $product->getIdentifier(),
            \DateTimeImmutable::createFromMutable($product->getCreated()),
            \DateTimeImmutable::createFromMutable($product->getUpdated()),
            $product->isEnabled(),
            $product->getFamily()->getCode(),
            $product->getCategoryCodes(),
            $product->getGroupCodes(),
            $product->isVariant() ? $product->getParent()->getCode() : null,
            self::productAssociationsAsArray($product),
            [],
            $product->getValues()
        );
    }

    public static function productAssociationsAsArray(ProductInterface $product): array
    {
        $association = [];
        foreach ($product->getAllAssociations() as $association) {
            $association[$association->getAssociationType()->getCode()] = [
                'products' => array_map(function (ProductInterface $product) {
                    return $product->getIdentifier();
                }, $association->getProducts()->toArray()),
                'product_models' => array_map(function (ProductModelInterface $productModel) {
                    return $productModel->getCode();
                }, $association->getProductModels()->toArray()),
                'groups' => array_map(function (GroupInterface $group) {
                }, $association->getGroups()->toArray())
            ];
        }

        return $association;
    }
}
