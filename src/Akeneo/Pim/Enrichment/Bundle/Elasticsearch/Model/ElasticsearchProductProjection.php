<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

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
    private $ancestors;

    /** @var null|array */
    private $label;

    /** @var array */
    private $attributesForAncestor;

    /** @var array */
    private $attributesForThisLevel;

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
        array $ancestors,
        ?array $label,
        array $attributesForAncestor,
        array $attributesForThisLevel,
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
        $this->ancestors = $ancestors;
        $this->label = $label;
        $this->attributesForAncestor = $attributesForAncestor;
        $this->attributesForThisLevel = $attributesForThisLevel;
        $this->additionalData = $additionalData;
    }

    public function addAdditionalData(string $key, $value): ElasticsearchProductProjection
    {
        $additionalData = $this->additionalData;
        $additionalData[$key] = $value;

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
            $this->ancestors,
            $this->label,
            $this->attributesForAncestor,
            $this->attributesForThisLevel,
            $additionalData
        );
    }

    public function toArray(): array
    {
        $in_group = null;
        if (!empty($this->groupCodes)) {
            $in_group = [];
            foreach ($this->groupCodes as $groupCode) {
                $in_group[$groupCode] = true;
            }
        }

        $family = null;
        if (null !== $this->familyCode) {
            $family = [
                'code'   => $this->familyCode,
                'labels' => $this->familyLabels,
            ];
        }

        $data = [
            'id' => sprintf('%s%s', self::INDEX_PREFIX_ID, $this->id),
            'identifier' => $this->identifier,
            'created' => $this->createdDate->format(self::INDEX_DATE_FORMAT),
            'updated' => $this->updatedDate->format(self::INDEX_DATE_FORMAT),
            'family' => $family,
            'enabled' => $this->isEnabled,
            'categories' => $this->categoryCodes,
            'categories_of_ancestors' => $this->categoryCodesOfAncestors,
            'groups' => $this->groupCodes,
            'completeness' => $this->completeness,
            'family_variant' => $this->familyVariantCode,
            'parent' => $this->parentProductModelCode,
            'values' => $this->values,
            'ancestors' => $this->ancestors,
            'label' => $this->label,
            'document_type' => ProductInterface::class,
            'attributes_of_ancestors' => $this->attributesForAncestor,
            'attributes_for_this_level' => $this->attributesForThisLevel,
        ];

        if ($in_group !== null) {
            $data['in_group'] = $in_group;
        }

        return array_merge($data, $this->additionalData);
    }

    /**
     * Temporary method (see TIP-1222) to build an IndexableProduct.
     *
     * @param ProductInterface                          $product
     * @param array                                     $activatedLocaleCodes
     * @param array                                     $channelCodes
     * @param array                                     $normalizedValues
     * @param ProductCompletenessCollection             $completenessCollection
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     *
     * @return ElasticsearchProductProjection
     */
    public static function fromProductReadModel(
        ProductInterface $product,
        array $activatedLocaleCodes,
        array $channelCodes,
        array $normalizedValues,
        ProductCompletenessCollection $completenessCollection,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ): ElasticsearchProductProjection {
        $familyLabels = null;
        $family = $product->getFamily();
        if (null !== $family && !empty($activatedLocaleCodes)) {
            $familyLabels = self::getFamilyLabels($family, $activatedLocaleCodes);
        }

        $label = [];
        if (null !== $family && null !== $family->getAttributeAsLabel()) {
            $label = $normalizedValues[sprintf('%s-text', $family->getAttributeAsLabel()->getCode())] ?? [];
        }

        $ancestorsCategories = [];
        if ($product->isVariant() && null !== $product->getParent()) {
            $ancestorsCategories = $product->getParent()->getCategoryCodes();
        }

        $normalizedCompleteness = [];
        foreach ($completenessCollection as $completeness) {
            $channelCode = $completeness->channelCode();
            $localeCode = $completeness->localeCode();
            $normalizedCompleteness[$channelCode][$localeCode] = $completeness->ratio();
        }

        return new self(
            (string) $product->getId(),
            $product->getIdentifier(),
            \DateTimeImmutable::createFromMutable($product->getCreated()),
            \DateTimeImmutable::createFromMutable(self::getUpdatedAt($product)),
            $product->isEnabled(),
            $product->getFamily() ? $product->getFamily()->getCode() : null,
            $familyLabels,
            $product->isVariant() && null !== $product->getFamilyVariant()
                ? $product->getFamilyVariant()->getCode()
                : null,
            $product->getCategoryCodes(),
            $ancestorsCategories,
            $product->getGroupCodes(),
            $normalizedCompleteness,
            $product->isVariant() && $product->getParent() ? $product->getParent()->getCode() : null,
            $normalizedValues,
            static::getAncestors($product, $activatedLocaleCodes, $channelCodes),
            $label,
            self::getAttributeCodesOfAncestors($product, $attributesProvider),
            self::getAttributeCodesForOwnLevel($product, $attributesProvider)
        );
    }

    private static function getUpdatedAt(ProductInterface $product): \DateTime
    {
        $date = $product->getUpdated();
        if ($product->isVariant()) {
            $dates = [$date];
            $parent = $product->getParent();
            while (null !== $parent) {
                $dates[] = $parent->getUpdated();
                $parent = $parent->getParent();
            }

            $date = max($dates);
        }

        return $date;
    }

    private static function getFamilyLabels(FamilyInterface $family, array $activatedLocaleCodes): array
    {
        $familyLabels = [];
        foreach ($activatedLocaleCodes as $activatedLocaleCode) {
            $translation = $family->getTranslation($activatedLocaleCode);
            if (method_exists($translation, 'getLabel')) {
                $familyLabels[$activatedLocaleCode] = $translation->getLabel();
            }
        }

        return $familyLabels;
    }

    private static function getAttributeCodesForOwnLevel(
        ProductInterface $product,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ): array {
        $attributeCodes = array_keys($product->getRawValues());
        $familyAttributesCodes = [];
        if ($product->isVariant()) {
            $familyAttributesCodes = array_map(function (AttributeInterface $attribute) {
                return $attribute->getCode();
            }, $attributesProvider->getAttributes($product));
        } elseif (null !== $product->getFamily()) {
            $familyAttributesCodes = $product->getFamily()->getAttributeCodes();
        }

        $attributeCodes = array_unique(array_merge($familyAttributesCodes, $attributeCodes));
        sort($attributeCodes);

        return $attributeCodes;
    }

    private static function getAttributeCodesOfAncestors(
        ProductInterface $product,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ): array {
        if (!$product->isVariant()) {
            return [];
        }

        $ancestorsAttributesCodes = [];
        $entityWithFamilyVariant = $product;

        while (null !== $entityWithFamilyVariant->getParent()) {
            $parent = $entityWithFamilyVariant->getParent();
            $attributeCodes = array_map(
                function (AttributeInterface $attribute) {
                    return $attribute->getCode();
                },
                $attributesProvider->getAttributes($parent)
            );

            $ancestorsAttributesCodes = array_merge($ancestorsAttributesCodes, $attributeCodes);

            $entityWithFamilyVariant = $parent;
        }

        sort($ancestorsAttributesCodes);

        return $ancestorsAttributesCodes;
    }

    private static function getAncestors(
        ProductInterface $product,
        array $activatedLocaleCodes,
        array $channelCodes
    ): array {
        return [
            'ids' => $product->isVariant() ? self::getAncestorsIds($product) : [],
            'codes' => $product->isVariant() ? self::getAncestorsCodes($product) : [],
            'labels' => $product->isVariant()
                ? self::getAncestorsLabels($product, $activatedLocaleCodes, $channelCodes)
                : [],
        ];
    }

    private static function getAncestorsIds(EntityWithFamilyVariantInterface $entityWithFamilyVariant): array
    {
        $ancestorsIds = [];
        while (null !== $parent = $entityWithFamilyVariant->getParent()) {
            $ancestorsIds[] = 'product_model_' . $parent->getId();
            $entityWithFamilyVariant = $parent;
        }

        return $ancestorsIds;
    }

    private static function getAncestorsCodes(EntityWithFamilyVariantInterface $entityWithFamilyVariant)
    {
        $ancestorsCodes = [];
        while (null !== $parent = $entityWithFamilyVariant->getParent()) {
            $ancestorsCodes[] = $parent->getCode();
            $entityWithFamilyVariant = $parent;
        }

        return $ancestorsCodes;
    }

    private static function getAncestorsLabels(
        EntityWithFamilyVariantInterface $entity,
        array $activatedLocaleCodes,
        array $channelCodes
    ): array {
        $family = $entity->getFamily();
        if (null === $family) {
            return [];
        }

        $attributeAsLabel = $family->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return [];
        }

        $ancestorsLabels = [];
        $attributeCodeAsLabel = $attributeAsLabel->getCode();
        switch (true) {
            case $attributeAsLabel->isScopable() && $attributeAsLabel->isLocalizable():
                $ancestorsLabels = self::getLocalizableAndScopableLabels(
                    $entity,
                    $attributeCodeAsLabel,
                    $activatedLocaleCodes,
                    $channelCodes
                );
                break;

            case $attributeAsLabel->isScopable():
                $ancestorsLabels = self::getScopableLabels($entity, $attributeCodeAsLabel, $channelCodes);
                break;

            case $attributeAsLabel->isLocalizable():
                $ancestorsLabels = self::getLocalizableLabels($entity, $attributeCodeAsLabel, $activatedLocaleCodes);
                break;

            default:
                $value = $entity->getValue($attributeCodeAsLabel);
                if (null !== $value) {
                    $ancestorsLabels['<all_channels>']['<all_locales>'] = $value->getData();
                }
                break;
        }

        return $ancestorsLabels;
    }

    private static function getLocalizableAndScopableLabels(
        EntityWithFamilyVariantInterface $entity,
        string $attributeCodeAsLabel,
        array $activatedLocaleCodes,
        array $channelCodes
    ): array {
        $ancestorsLabels = [];
        // @todo: how to load the channel codes?
        foreach ($channelCodes as $channelCode) {
            foreach ($activatedLocaleCodes as $localeCode) {
                $value = $entity->getValue($attributeCodeAsLabel, $localeCode, $channelCode);
                if (null !== $value) {
                    $ancestorsLabels[$channelCode][$localeCode] = $value->getData();
                }
            }
        }

        return $ancestorsLabels;
    }

    private static function getScopableLabels(
        EntityWithFamilyVariantInterface $entity,
        string $attributeCodeAsLabel,
        array $channelCodes
    ): array {
        $ancestorsLabels = [];
        // @todo: how to load the channel codes?
        foreach ($channelCodes as $channelCode) {
            $value = $entity->getValue($attributeCodeAsLabel, null, $channelCode);
            if (null !== $value) {
                $ancestorsLabels[$channelCode]['<all_locales>'] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }

    private static function getLocalizableLabels(EntityWithFamilyVariantInterface $entity, string $attributeCodeAsLabel, array $activatedLocaleCodes): array
    {
        $ancestorsLabels = [];
        foreach ($activatedLocaleCodes as $localeCode) {
            $value = $entity->getValue($attributeCodeAsLabel, $localeCode);
            if (null !== $value) {
                $ancestorsLabels['<all_channels>'][$localeCode] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }
}
