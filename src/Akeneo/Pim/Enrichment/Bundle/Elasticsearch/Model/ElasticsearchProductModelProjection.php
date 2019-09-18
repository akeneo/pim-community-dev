<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ElasticsearchProductModelProjection
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

    /** @var int[] */
    private $ancestorIds;

    /** @var string[] */
    private $ancestorCodes;

    /** @var string[] */
    private $ancestorLabels;

    /** @var string */
    private $label;

    /** @var string[] */
    private $ancestorAttributeCodes;

    /** @var string[] */
    private $attributesForThisLevel;

    /** @var array */
    private $additionalData;

    public function __construct(
        int $id,
        string $code,
        \DateTimeImmutable $createdDate,
        \DateTimeImmutable $updatedDate,
        string $familyCode,
        string $familyVariantCode,
        array $categoryCodes,
        array $ancestorCategoryCodes,
        ?string $parentCode,
        array $values,
        array $allComplete,
        array $allIncomplete,
        array $ancestorIds,
        array $ancestorCodes,
        array $ancestorLabels,
        string $label,
        array $ancestorAttributeCodes,
        array $attributesForThisLevel,
        array $additionalData = []
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->familyCode = $familyCode;
        $this->familyVariantCode = $familyVariantCode;
        $this->categoryCodes = $categoryCodes;
        $this->ancestorCategoryCodes = $ancestorCategoryCodes;
        $this->parentCode = $parentCode;
        $this->values = $values;
        $this->allComplete = $allComplete;
        $this->allIncomplete = $allIncomplete;
        $this->ancestorIds = $ancestorIds;
        $this->ancestorCodes = $ancestorCodes;
        $this->ancestorLabels = $ancestorLabels;
        $this->label = $label;
        $this->ancestorAttributeCodes = $ancestorAttributeCodes;
        $this->attributesForThisLevel = $attributesForThisLevel;
        $this->additionalData = $additionalData;
    }

    public function addAdditionalData(string $key, $value): ElasticsearchProductModelProjection
    {
        $additionalData = $this->additionalData;
        $additionalData[$key] = $value;

        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->familyCode,
            $this->familyVariantCode,
            $this->categoryCodes,
            $this->parentCode,
            $this->values,
            $this->allComplete,
            $this->allIncomplete,
            $this->ancestorIds,
            $this->ancestorLabels,
            $this->label,
            $this->ancestorAttributeCodes,
            $this->attributesForThisLevel,
            $additionalData
        );
    }

    public function toArray(): array
    {
        return array_merge([
            'id' => 'product_model_' . (string) $this->id,
            'identifier' => $this->code,
            'created' => $this->createdDate->format(self::INDEX_DATE_FORMAT),
            'updated' => $this->updatedDate->format(self::INDEX_DATE_FORMAT),
            'family' => $this->familyCode,
            'family_variant' => $this->familyVariantCode,
            'categories' => $this->categoryCodes,
            'categories_of_ancestors' => $this->ancestorCategoryCodes,
            'parent' => $this->parentCode,
            'values' => $this->values,
            'all_complete' => $this->allComplete,
            'all_incomplete' => $this->allIncomplete,
            'ancestors' => [
                'ids' => $this->ancestorIds,
                'codes' => $this->ancestorCodes,
                'labels' => $this->ancestorLabels,
            ],
            'label' => $this->label,
            'document_type' => ProductModelInterface::class,
            'attributes_of_ancestors' => $this->ancestorAttributeCodes,
            'attributes_for_this_level' => $this->attributesForThisLevel,
        ], $this->additionalData);
    }
}
