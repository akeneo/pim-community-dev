<?php

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindRecordsUsedAsProductVariantAxisInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository as InMemoryProductAttributeRepository;
use Akeneo\Test\Acceptance\FamilyVariant\InMemoryFamilyVariantsByAttributeAxes;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;

class InMemoryFindRecordsUsedAsProductVariantAxis implements FindRecordsUsedAsProductVariantAxisInterface
{
    private InMemoryFamilyVariantsByAttributeAxes $familyVariantsByAttributeAxes;
    private InMemoryProductAttributeRepository $attributeRepository;
    private InMemoryProductRepository $productRepository;

    public function __construct(
        InMemoryFamilyVariantsByAttributeAxes $familyVariantsByAttributeAxes,
        InMemoryProductAttributeRepository $attributeRepository,
        InMemoryProductRepository $productRepository
    ) {
        $this->familyVariantsByAttributeAxes = $familyVariantsByAttributeAxes;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
    }

    public function areUsed(array $recordCodes, string $referenceEntityIdentifier): bool
    {
        $attributeCodes = $this->findProductAttributeCodesLinkedToReferenceEntity($referenceEntityIdentifier);

        foreach ($attributeCodes as $attributeCode) {
            $familyVariantsIdentifiers = $this->familyVariantsByAttributeAxes->findIdentifiers([$attributeCode]);

            if (empty($familyVariantsIdentifiers)) {
                continue;
            }

            if (
                0 !== $this->countEntitiesUsingRecordAsProductVariantAxis(
                    $familyVariantsIdentifiers,
                    $attributeCode,
                    $recordCodes
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function getUsedCodes(array $recordCodes, string $referenceEntityIdentifier): array
    {
        $attributeCodes = $this->findProductAttributeCodesLinkedToReferenceEntity($referenceEntityIdentifier);
        $recordCodesUsedAsAxis = [];

        foreach ($attributeCodes as $attributeCode) {
            $familyVariantsIdentifiers = $this->familyVariantsByAttributeAxes->findIdentifiers([$attributeCode]);

            if (empty($familyVariantsIdentifiers)) {
                continue;
            }

            foreach ($recordCodes as $recordCode) {
                if (
                    0 !== $this->countEntitiesUsingRecordAsProductVariantAxis(
                        $familyVariantsIdentifiers,
                        $attributeCode,
                        $recordCodes
                    )
                ) {
                    $recordCodesUsedAsAxis[] = $recordCode;
                }
            }
        }

        return $recordCodesUsedAsAxis;
    }

    private function findProductAttributeCodesLinkedToReferenceEntity(
        string $referenceEntityIdentifier
    ): array {
        $identifier = (string)$referenceEntityIdentifier;

        return array_merge(
            $this->findAttributeCodeForType(ReferenceEntityType::REFERENCE_ENTITY, $identifier),
            $this->findAttributeCodeForType(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION, $identifier)
        );
    }

    private function findAttributeCodeForType(string $attributeType, string $identifier): array
    {
        $results = [];

        /** @var AttributeInterface[] $attributes */
        $attributes = $this->attributeRepository->findBy([
            'attributeType' => $attributeType,
        ]);

        foreach ($attributes as $attribute) {
            if ($attribute->getProperty('reference_data_name') === $identifier) {
                $results[] = $attribute->getCode();
            }
        }

        return $results;
    }

    private function countEntitiesUsingRecordAsProductVariantAxis(
        array $familyVariantsIdentifier,
        string $attributeCode,
        array $recordCodes
    ): int {
        /** @var Product[] $products */
        $products = $this->productRepository->findAll();

        $count = 0;

        foreach ($products as $product) {
            if (
                in_array($product->getFamilyVariant(), $familyVariantsIdentifier)
                && in_array($product->getValue($attributeCode)->getData(), $recordCodes)
            ) {
                $count++;
            }
        }

        return $count;
    }
}
