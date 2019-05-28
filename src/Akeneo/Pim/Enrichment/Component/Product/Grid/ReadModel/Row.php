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

    /** @var string */
    private $identifier;

    /** @var string */
    private $familyCode;

    /** @var string[] */
    private $groupCodes;

    /** @var boolean */
    private $enabled;

    /** @var \DateTimeInterface */
    private $created;

    /** @var \DateTimeInterface */
    private $updated;

    /** @var string */
    private $label;

    /** @var null|object  */
    private $image;

    /** @var null|int */
    private $completeness;

    /** @var string */
    private $documentType;

    /** @var integer */
    private $technicalId;

    /** @var string */
    private $searchId;

    /** @var bool */
    private $checked;

    /** @var array */
    private $childrenCompleteness;

    /** @var null|string */
    private $parent;

    /** @var WriteValueCollection */
    private $values;

    /** @var AdditionalProperties */
    private $additionalProperties;

    /**
     * @param string                   $identifier
     * @param null|string              $family
     * @param string[]                 $groupCodes
     * @param bool|null                $enabled
     * @param \DateTimeInterface       $created
     * @param \DateTimeInterface       $updated
     * @param string                   $label
     * @param null|object              $image
     * @param null|int                 $completeness
     * @param string                   $documentType
     * @param int                      $technicalId
     * @param string                   $searchId
     * @param bool                     $checked
     * @param array                    $childrenCompleteness
     * @param null|string              $parentCode
     * @param WriteValueCollection $values
     * @param AdditionalProperties     $additionalProperties
     */
    private function __construct(
        string $identifier,
        ?string $family,
        array $groupCodes,
        ?bool $enabled,
        \DateTimeInterface $created,
        \DateTimeInterface $updated,
        ?string $label,
        ?object $image,
        ?int $completeness,
        string $documentType,
        int $technicalId,
        string $searchId,
        ?bool $checked,
        array $childrenCompleteness,
        ?string $parentCode,
        WriteValueCollection $values,
        AdditionalProperties $additionalProperties
    ) {
        $this->identifier = $identifier;
        $this->familyCode = $family;
        $this->groupCodes = $groupCodes;
        $this->enabled = $enabled;
        $this->created = $created;
        $this->updated = $updated;
        $this->label = $label;
        $this->image = $image;
        $this->completeness = $completeness;
        $this->documentType = $documentType;
        $this->technicalId = $technicalId;
        $this->searchId = $searchId;
        $this->checked = $checked;
        $this->childrenCompleteness = $childrenCompleteness;
        $this->parent = $parentCode;
        $this->values = $values;
        $this->additionalProperties = $additionalProperties;
    }

    public static function fromProduct(
        string $identifier,
        ?string $familyCode,
        array $groupCodes,
        ?bool $enabled,
        \DateTimeInterface $created,
        \DateTimeInterface $updated,
        string $label,
        ?object $image,
        ?int $completeness,
        int $technicalId,
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
        ?string $parent,
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
            $parent,
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
            $this->parent,
            $this->values,
            $properties
        );
    }

    /**
     * @return string
     */
    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return null|string
     */
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

    /**
     * @return bool|null
     */
    public function enabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @return \DateTimeInterface
     */
    public function created(): \DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @return \DateTimeInterface
     */
    public function updated(): \DateTimeInterface
    {
        return $this->updated;
    }

    /**
     * @return string
     */
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

    /**
     * @return null|int
     */
    public function completeness(): ?int
    {
        return $this->completeness;
    }

    /**
     * @return string
     */
    public function documentType(): string
    {
        return $this->documentType;
    }

    /**
     * @return int
     */
    public function technicalId(): int
    {
        return $this->technicalId;
    }

    /**
     * @return string
     */
    public function searchId(): string
    {
        return $this->searchId;
    }

    /**
     * @return bool
     */
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

    /**
     * @return null|string
     */
    public function parentCode(): ?string
    {
        return $this->parent;
    }

    /**
     * @return WriteValueCollection
     */
    public function values(): WriteValueCollection
    {
        return $this->values;
    }

    /**
     * @return AdditionalProperties
     */
    public function additionalProperties(): AdditionalProperties
    {
        return $this->additionalProperties;
    }
}
