<?php

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordIsUsedAsProductVariantAxisInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository as InMemoryProductAttributeRepository;
use Akeneo\Test\Acceptance\FamilyVariant\InMemoryFamilyVariantsByAttributeAxes;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;

class InMemoryRecordIsUsedAsProductVariantAxis implements RecordIsUsedAsProductVariantAxisInterface
{
    /** @var InMemoryFamilyVariantsByAttributeAxes */
    private $familyVariantsByAttributeAxes;

    /** @var InMemoryProductAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryProductRepository */
    private $productRepository;

    public function __construct(
        InMemoryFamilyVariantsByAttributeAxes $familyVariantsByAttributeAxes,
        InMemoryProductAttributeRepository $attributeRepository,
        InMemoryProductRepository $productRepository
    ) {
        $this->familyVariantsByAttributeAxes = $familyVariantsByAttributeAxes;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
    }

    public function execute(RecordCode $recordCode, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        $attributeCodes = $this->findProductAttributeCodesLinkedToReferenceEntity($referenceEntityIdentifier);

        foreach ($attributeCodes as $attributeCode) {
            $familyVariantsIdentifiers = $this->familyVariantsByAttributeAxes->findIdentifiers([$attributeCode]);

            if (empty($familyVariantsIdentifiers)) {
                continue;
            }

            if (0 !== $this->countEntitiesUsingRecordAsProductVariantAxis(
                    $familyVariantsIdentifiers,
                    $attributeCode,
                    $recordCode)
            ) {
                return true;
            }
        }

        return false;
    }

    private function findProductAttributeCodesLinkedToReferenceEntity(
        ReferenceEntityIdentifier $referenceEntityIdentifier
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
        RecordCode $recordCode
    ): int {
        /** @var Product[] $products */
        $products = $this->productRepository->findAll();

        $count = 0;

        foreach ($products as $product) {
            if (in_array($product->getFamilyVariant(), $familyVariantsIdentifier)
                && $product->getValue($attributeCode)->getData() === (string)$recordCode) {
                $count++;
            }
        }

        return $count;
    }
}
