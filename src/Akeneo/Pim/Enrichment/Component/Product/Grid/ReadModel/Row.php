<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Row
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $family;

    /** @var string[] */
    private $groups;

    /** @var boolean */
    private $enabled;

    /** @var \DateTimeInterface */
    private $created;

    /** @var \DateTimeInterface */
    private $updated;

    /** @var null|string */
    private $label;

    /** @var null|string */
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

    /** @var null|bool */
    private $completeVariantProduct;

    /** @var null|string */
    private $parent;

    /** @var ValueCollection */
    private $values;

    /**
     * @param string             $identifier
     * @param string             $family
     * @param string[]           $groups
     * @param bool               $enabled
     * @param \DateTimeInterface $created
     * @param \DateTimeInterface $updated
     * @param null|string        $label
     * @param null|string        $image
     * @param null|int           $completeness
     * @param string             $documentType
     * @param int                $technicalId
     * @param string             $searchId
     * @param bool               $checked
     * @param bool|null          $completeVariantProduct
     * @param null|string        $parent
     * @param ValueCollection    $values
     */
    public function __construct(
        string $identifier,
        string $family,
        array $groups,
        bool $enabled,
        \DateTimeInterface $created,
        \DateTimeInterface $updated,
        ?string $label,
        ?string $image,
        ?int $completeness,
        string $documentType,
        int $technicalId,
        string $searchId,
        ?bool $checked,
        ?bool $completeVariantProduct,
        ?string $parent,
        ValueCollection $values
    ) {
        $this->identifier = $identifier;
        $this->family = $family;
        $this->groups = $groups;
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
        $this->completeVariantProduct = $completeVariantProduct;
        $this->parent = $parent;
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function family(): string
    {
        return $this->family;
    }

    /**
     * @return string[]
     */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * @return bool
     */
    public function enabled(): bool
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
     * @return null|ScalarValue
     */
    public function label(): ?ScalarValue
    {
        return $this->label;
    }

    /**
     * @return null|MediaValue
     */
    public function image(): ?MediaValue
    {
        return $this->image;
    }

    /**
     * @return int
     */
    public function completeness(): int
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
     * @return bool|null
     */
    public function isCompleteVariantProduct(): ?bool
    {
        return $this->completeVariantProduct;
    }

    /**
     * @return null|string
     */
    public function parent(): ?string
    {
        return $this->parent;
    }

    /**
     * @return ValueCollection
     */
    public function values(): ValueCollection
    {
        return $this->values;
    }
}
