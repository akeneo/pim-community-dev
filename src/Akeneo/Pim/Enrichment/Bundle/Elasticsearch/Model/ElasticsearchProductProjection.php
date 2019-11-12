<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ElasticsearchProductProjection
{
    private const INDEX_PREFIX_ID   = 'product_';
    private const INDEX_DATE_FORMAT = 'c';

    /** @var string */
    private $id;

    /** @var string */
    private $identifier;

    /** @var \DateTimeImmutable */
    private $createdDate;

    /** @var \DateTimeImmutable */
    private $updatedDate;

    /** @var bool */
    private $isEnabled;

    /** @var null|string */
    private $familyCode;

    /** @var null|array */
    private $familyLabels;

    /** @var null|string */
    private $familyVariantCode;

    /** @var array */
    private $categoryCodes;

    /** @var array */
    private $categoryCodesOfAncestors;

    /** @var array */
    private $groupCodes;

    /** @var array */
    private $completeness;

    /** @var null|string */
    private $parentProductModelCode;

    /** @var array */
    private $values;

    /** @var array */
    private $ancestorsIds;

    /** @var array */
    private $ancestorsCodes;

    /** @var null|array */
    private $label;

    /** @var array */
    private $attributeCodesForAncestor;

    /** @var array */
    private $attributeCodesForThisLevel;

    /** @var array */
    private $additionalData = [];

    public function __construct(
        string $id,
        string $identifier,
        \DateTimeImmutable $createdDate,
        \DateTimeImmutable $updatedDate,
        bool $isEnabled,
        ?string $familyCode,
        ?array $familyLabels,
        ?string $familyVariantCode,
        array $categoryCodes,
        array $categoryCodesOfAncestors,
        array $groupCodes,
        array $completeness,
        ?string $parentProductModelCode,
        array $values,
        array $ancestorIds,
        array $ancestorCodes,
        array $label,
        array $attributeCodesForAncestor,
        array $attributeCodesForThisLevel,
        array $additionalData = []
    ) {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->isEnabled = $isEnabled;
        $this->familyCode = $familyCode;
        $this->familyLabels = $familyLabels;
        $this->familyVariantCode = $familyVariantCode;
        $this->categoryCodes = $categoryCodes;
        $this->categoryCodesOfAncestors = $categoryCodesOfAncestors;
        $this->groupCodes = $groupCodes;
        $this->completeness = $completeness;
        $this->parentProductModelCode = $parentProductModelCode;
        $this->values = $values;
        $this->ancestorsIds = $ancestorIds;
        $this->ancestorsCodes = $ancestorCodes;
        $this->label = $label;
        $this->attributeCodesForAncestor = $attributeCodesForAncestor;
        $this->attributeCodesForThisLevel = $attributeCodesForThisLevel;
        $this->additionalData = $additionalData;
    }

    public function addAdditionalData(array $additionalData): ElasticsearchProductProjection
    {
        $additionalData = array_merge($this->additionalData, $additionalData);

        return new self(
            $this->id,
            $this->identifier,
            $this->createdDate,
            $this->updatedDate,
            $this->isEnabled,
            $this->familyCode,
            $this->familyLabels,
            $this->familyVariantCode,
            $this->categoryCodes,
            $this->categoryCodesOfAncestors,
            $this->groupCodes,
            $this->completeness,
            $this->parentProductModelCode,
            $this->values,
            $this->ancestorsIds,
            $this->ancestorsCodes,
            $this->label,
            $this->attributeCodesForAncestor,
            $this->attributeCodesForThisLevel,
            $additionalData
        );
    }

    public function toArray(): array
    {
        $inGroup = null;
        if (!empty($this->groupCodes)) {
            $inGroup = [];
            foreach ($this->groupCodes as $groupCode) {
                $inGroup[$groupCode] = true;
            }
        }

        $familyCode = null;
        if (null !== $this->familyCode) {
            $familyCode = [
                'code'   => $this->familyCode,
                'labels' => $this->familyLabels,
            ];
        }

        $data = [
            'id' => sprintf('%s%s', self::INDEX_PREFIX_ID, $this->id),
            'identifier' => $this->identifier,
            'created' => $this->createdDate->format(self::INDEX_DATE_FORMAT),
            'updated' => $this->updatedDate->format(self::INDEX_DATE_FORMAT),
            'family' => $familyCode,
            'enabled' => $this->isEnabled,
            'categories' => $this->categoryCodes,
            'categories_of_ancestors' => $this->categoryCodesOfAncestors,
            'groups' => $this->groupCodes,
            'completeness' => $this->completeness,
            'family_variant' => $this->familyVariantCode,
            'parent' => $this->parentProductModelCode,
            'values' => $this->values,
            'ancestors' => [
                'ids' => preg_filter('/^/', 'product_model_', $this->ancestorsIds),
                'codes' => $this->ancestorsCodes,
                'labels' => null !== $this->parentProductModelCode ? $this->label : [],
            ],
            'label' => $this->label,
            'document_type' => ProductInterface::class,
            'attributes_of_ancestors' => $this->attributeCodesForAncestor,
            'attributes_for_this_level' => $this->attributeCodesForThisLevel,
        ];

        if ($inGroup !== null) {
            $data['in_group'] = $inGroup;
        }

        return array_merge($data, $this->additionalData);
    }
}
