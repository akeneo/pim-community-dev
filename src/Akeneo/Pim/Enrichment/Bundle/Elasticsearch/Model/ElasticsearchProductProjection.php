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

    private string $id;
    private string $identifier;
    private \DateTimeImmutable $createdDate;
    private \DateTimeImmutable $updatedDate;
    private \DateTimeImmutable $entityUpdatedDate;
    private bool $isEnabled;
    private ?string $familyCode;
    private ?array $familyLabels;
    private ?string $familyVariantCode;
    private array $categoryCodes;
    private array $categoryCodesOfAncestors;
    private array $groupCodes;
    private array $completeness;
    private ?string $parentProductModelCode;
    private array $values;
    private array $ancestorsIds;
    private array $ancestorsCodes;
    private ?array $label;
    private array $attributeCodesForAncestor;
    private array $attributeCodesForThisLevel;
    private array $additionalData = [];

    public function __construct(
        string $id,
        string $identifier,
        \DateTimeImmutable $createdDate,
        \DateTimeImmutable $updatedDate,
        \DateTimeImmutable $entityUpdatedDate,
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
        $this->entityUpdatedDate = $entityUpdatedDate;
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
            $this->entityUpdatedDate,
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
            'entity_updated' => $this->entityUpdatedDate->format(self::INDEX_DATE_FORMAT),
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
