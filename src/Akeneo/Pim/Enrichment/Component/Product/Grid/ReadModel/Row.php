<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Row
{
    private const PRODUCT_TYPE = 'product';
    private const PRODUCT_MODEL_TYPE = 'product_model';

    private function __construct(
        private ?string $identifier,
        private ?string $familyCode,
        private array $groupCodes,
        private ?bool $enabled,
        private \DateTimeInterface $created,
        private \DateTimeInterface $updated,
        private ?string $label,
        private ?object $image,
        private ?int $completeness,
        private string $documentType,
        private int|string $technicalId,
        private string $searchId,
        private ?bool $checked,
        private array $childrenCompleteness,
        private ?string $parentCode,
        private WriteValueCollection $values,
        private AdditionalProperties $additionalProperties
    ) {
    }

    public static function fromProduct(
        ?string $identifier,
        ?string $familyCode,
        array $groupCodes,
        ?bool $enabled,
        \DateTimeInterface $created,
        \DateTimeInterface $updated,
        string $label,
        ?object $image,
        ?int $completeness,
        string $technicalId,
        ?string $parentCode,
        WriteValueCollection $values
    ):self {
        return new self(
            $identifier,
            $familyCode,
            $groupCodes,
            $enabled,
            $created,
            $updated,
            $label,
            $image,
            $completeness,
            self::PRODUCT_TYPE,
            $technicalId,
            sprintf('%s_%s', self::PRODUCT_TYPE, $technicalId),
            true,
            [],
            $parentCode,
            $values,
            new AdditionalProperties()
        );
    }

    public static function fromProductModel(
        string $code,
        string $familyCode,
        \DateTimeInterface $created,
        \DateTimeInterface $updated,
        string $label,
        ?object $image,
        int $technicalId,
        array $childrenCompleteness,
        ?string $parentCode,
        WriteValueCollection $values
    ):self {
        return new self(
            $code,
            $familyCode,
            [],
            false,
            $created,
            $updated,
            $label,
            $image,
            null,
            self::PRODUCT_MODEL_TYPE,
            $technicalId,
            sprintf('%s_%s', self::PRODUCT_MODEL_TYPE, $technicalId),
            true,
            $childrenCompleteness,
            $parentCode,
            $values,
            new AdditionalProperties()
        );
    }

    public function addAdditionalProperty(AdditionalProperty $property): Row
    {
        $properties = $this->additionalProperties->addAdditionalProperty($property);

        return new self(
            $this->identifier,
            $this->familyCode,
            $this->groupCodes,
            $this->enabled,
            $this->created,
            $this->updated,
            $this->label,
            $this->image,
            $this->completeness,
            $this->documentType,
            $this->technicalId,
            $this->searchId,
            $this->checked,
            $this->childrenCompleteness,
            $this->parentCode,
            $this->values,
            $properties
        );
    }

    public function identifier(): ?string
    {
        return $this->identifier;
    }

    public function familyCode(): ?string
    {
        return $this->familyCode;
    }

    /**
     * @return string[]
     */
    public function groupCodes(): array
    {
        return $this->groupCodes;
    }

    public function enabled(): ?bool
    {
        return $this->enabled;
    }

    public function created(): \DateTimeInterface
    {
        return $this->created;
    }

    public function updated(): \DateTimeInterface
    {
        return $this->updated;
    }

    public function label(): string
    {
        return $this->label;
    }

    /**
     * This method return a type object because in EE it can return
     * a MediaValue|ReferenceDataCollectionValue
     *
     * @return null|object
     */
    public function image(): ?object
    {
        return $this->image;
    }

    public function completeness(): ?int
    {
        return $this->completeness;
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    public function technicalId(): int|string
    {
        return $this->technicalId;
    }

    public function searchId(): string
    {
        return $this->searchId;
    }

    public function checked(): bool
    {
        return $this->checked;
    }

    /**
     * @return array
     */
    public function childrenCompleteness(): array
    {
        return $this->childrenCompleteness;
    }

    public function parentCode(): ?string
    {
        return $this->parentCode;
    }

    public function values(): WriteValueCollection
    {
        return $this->values;
    }

    public function additionalProperties(): AdditionalProperties
    {
        return $this->additionalProperties;
    }
}
