<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Ideally, we should have business events on deleted values and not use the raw_values to detect the change.
 * It would ease the detection of deleted unique values, and therefore their deletion in the table guaranteeing the uniqueness.
 *
 * Unique value attribute cannot exist in a product model.
 * Unique values are neither localizable nor scopable.
 */
final class GetAttributeCodesOfDeletedUniqueProductValues
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array list of attribute codes
     */
    public function compute(ProductInterface $product): array
    {
        $attributeCodesToDelete = [];
        foreach ($product->getValuesForVariation() as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
            if ($attribute->isUnique() && !$value->hasData() ) {
                $attributeCodesToDelete[] = $attribute->getCode();
            }
        }

        $attributeCodesBeforeModification = array_keys($product->getRawValues());
        $attributeCodesAfterModification = $product->getValuesForVariation()->getAttributeCodes();
        $attributeCodesToDelete = array_diff($attributeCodesBeforeModification, $attributeCodesAfterModification);

        $attributeCodesToDelete = array_filter($attributeCodesToDelete, function(string $attributeCodeToDelete) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCodeToDelete);

            return $attribute->isUnique();
        });

        return array_values(
            array_map(
                function ($attributeCodeToDelete) { return (string) $attributeCodeToDelete; },
                $attributeCodesToDelete
            )
        );
    }


}
