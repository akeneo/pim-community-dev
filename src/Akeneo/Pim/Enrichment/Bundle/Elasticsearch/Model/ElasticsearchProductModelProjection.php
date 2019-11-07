<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ElasticsearchProductModelProjection
{
    private const INDEX_DATE_FORMAT = 'c';

    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var \DateTimeImmutable */
    private $createdDate;

    /** @var \DateTimeImmutable */
    private $updatedDate;

    /** @var string */
    private $familyCode;

    /** @var string[] */
    private $familyLabels;

    /** @var string */
    private $familyVariantCode;

    /** @var string[] */
    private $categoryCodes;

    /** @var string[] */
    private $ancestorCategoryCodes;

    /** @var string|null */
    private $parentCode;

    /** @var array */
    private $values;

    /** @var array */
    private $allComplete;

    /** @var array */
    private $allIncomplete;

    /** @var int|null */
    private $parentId;

    /** @var array */
    private $labels;

    /** @var string[] */
    private $ancestorAttributeCodes;

    /** @var string[] */
    private $attributesForThisLevel;

    /** @var array */
    private $additionalData = [];

    public function __construct(
        int $id,
        string $code,
        \DateTimeImmutable $createdDate,
        \DateTimeImmutable $updatedDate,
        string $familyCode,
        array $familyLabels,
        string $familyVariantCode,
        array $categoryCodes,
        array $ancestorCategoryCodes,
        ?string $parentCode,
        array $values,
        array $allComplete,
        array $allIncomplete,
        ?int $parentId,
        array $labels,
        array $ancestorAttributeCodes,
        array $attributesForThisLevel,
        array $additionalData = []
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->familyCode = $familyCode;
        $this->familyLabels = $familyLabels;
        $this->familyVariantCode = $familyVariantCode;
        $this->categoryCodes = $categoryCodes;
        $this->ancestorCategoryCodes = $ancestorCategoryCodes;
        $this->parentCode = $parentCode;
        $this->values = $values;
        $this->allComplete = $allComplete;
        $this->allIncomplete = $allIncomplete;
        $this->parentId = $parentId;
        $this->labels = $labels;
        $this->ancestorAttributeCodes = $ancestorAttributeCodes;
        $this->attributesForThisLevel = $attributesForThisLevel;
        $this->additionalData = $additionalData;
    }

    public function addAdditionalData(array $additionalData): ElasticsearchProductModelProjection
    {
        $additionalData = array_merge($this->additionalData, $additionalData);

        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->familyCode,
            $this->familyLabels,
            $this->familyVariantCode,
            $this->categoryCodes,
            $this->ancestorCategoryCodes,
            $this->parentCode,
            $this->values,
            $this->allComplete,
            $this->allIncomplete,
            $this->parentId,
            $this->labels,
            $this->ancestorAttributeCodes,
            $this->attributesForThisLevel,
            $additionalData
        );
    }

    public function toArray(): array
    {
        return [
            'id' => 'product_model_' . (string) $this->id,
            'identifier' => $this->code,
            'created' => $this->createdDate->format(self::INDEX_DATE_FORMAT),
            'updated' => $this->updatedDate->format(self::INDEX_DATE_FORMAT),
            'family' => [
                'code' => $this->familyCode,
                'labels' => $this->familyLabels,
            ],
            'family_variant' => $this->familyVariantCode,
            'categories' => $this->categoryCodes,
            'categories_of_ancestors' => $this->ancestorCategoryCodes,
            'parent' => $this->parentCode,
            'values' => $this->values,
            'all_complete' => $this->allComplete,
            'all_incomplete' => $this->allIncomplete,
            'ancestors' => [
                'ids' => null !== $this->parentId ? ['product_model_' . (string) $this->parentId] : [],
                'codes' => null !== $this->parentCode ? [$this->parentCode] : [],
                'labels' => null !== $this->parentId ? $this->labels : [],
            ],
            'label' => $this->labels,
            'document_type' => ProductModelInterface::class,
            'attributes_of_ancestors' => $this->ancestorAttributeCodes,
            'attributes_for_this_level' => $this->attributesForThisLevel,
        ];
    }
}
