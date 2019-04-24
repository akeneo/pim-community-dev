<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;

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
    private $familyVariantCode;

    /** @var array */
    private $metadata;

    /** @var array */
    private $associations;

    /** @var array */
    private $categoryCodes;

    /** @var ValueCollectionInterface */
    private $values;

    public function __construct(
        int $id,
        string $code,
        \DateTimeInterface $createdDate,
        \DateTimeInterface $updatedDate,
        ?string $parentCode,
        string $familyVariantCode,
        array $metadata,
        array $associations,
        array $categoryCodes,
        ValueCollectionInterface $values
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->parentCode = $parentCode;
        $this->familyVariantCode = $familyVariantCode;
        $this->metadata = $metadata;
        $this->associations = $associations;
        $this->categoryCodes = $categoryCodes;
        $this->values = $values;
    }

    public static function fromWriteModel(ProductModelInterface $productModel, ValueCollectionInterface $values, array $metadata = []): ConnectorProductModel
    {
        return new self(
            $productModel->getId(),
            $productModel->getCode(),
            \DateTimeImmutable::createFromMutable($productModel->getCreated()),
            \DateTimeImmutable::createFromMutable($productModel->getUpdated()),
            (null !== $productModel->getParent()) ? $productModel->getParent()->getCode() : null,
            $productModel->getFamilyVariant()->getCode(),
            $metadata,
            self::productModelAssociationsAsArray($productModel),
            $productModel->getCategoryCodes(),
            $values
        );
    }

    public static function productModelAssociationsAsArray(ProductModelInterface $productModel): array
    {
        $associations = [];
        foreach ($productModel->getAllAssociations() as $association) {
            $associations[$association->getAssociationType()->getCode()]['products'][] = $association->getProducts()->map(function (ProductInterface $associatedProduct) {
                return $associatedProduct->getIdentifier();
            })->toArray();
            $associations[$association->getAssociationType()->getCode()]['product_models'][] = $association->getProductModels()->map(function (ProductModelInterface $associatedProductModel) {
                return $associatedProductModel->getCode();
            })->toArray();
            $associations[$association->getAssociationType()->getCode()]['groups'][] = $association->getGroups()->map(function (GroupInterface $associatedGroup) {
                return $associatedGroup->getCode();
            })->toArray();
        }

        return $associations;
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

    public function values(): ValueCollectionInterface
    {
        return $this->values;
    }
}
