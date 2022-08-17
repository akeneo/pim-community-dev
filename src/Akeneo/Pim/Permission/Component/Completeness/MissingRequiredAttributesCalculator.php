<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Query\GetRawValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;

final class MissingRequiredAttributesCalculator implements MissingRequiredAttributesCalculatorInterface
{
    private GetCompletenessProductMasks $getCompletenessProductMasks;
    private GetRequiredAttributesMasks $getRequiredAttributesMasks;
    private WriteValueCollectionFactory $writeValueCollectionFactory;
    private GetRawValues $getRawValues;

    public function __construct(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks,
        WriteValueCollectionFactory $writeValueCollectionFactory,
        GetRawValues $getRawValues
    ) {
        $this->getCompletenessProductMasks = $getCompletenessProductMasks;
        $this->getRequiredAttributesMasks = $getRequiredAttributesMasks;
        $this->writeValueCollectionFactory = $writeValueCollectionFactory;
        $this->getRawValues = $getRawValues;
    }

    /**
     * {@inheritDoc}
     */
    public function fromEntityWithFamily(
        EntityWithFamilyInterface $entityWithFamily
    ): ProductCompletenessWithMissingAttributeCodesCollection {
        $entityId = \method_exists($entityWithFamily, 'getUuid')
            ?  $entityWithFamily->getUuid()->toString()
            : (string) $entityWithFamily->getId();
        if (null === $entityWithFamily->getFamily()) {
            return new ProductCompletenessWithMissingAttributeCodesCollection($entityId, []);
        }
        $familyCode = $entityWithFamily->getFamily()->getCode();
        $requiredAttributesMasks = $this->getRequiredAttributesMasks->fromFamilyCodes([$familyCode]);

        // PIM-10059: if a user has no permission on an attribute group, the value collection inside the
        // product is filtered according to the permission. So the values appear as missing during the completeness
        // calculation. To avoid that, we build again the value collection from the raw values.
        $valuesWithFullPermissions = $this->writeValueCollectionFactory->createFromStorageFormat(
            $this->getRawValues($entityWithFamily)
        );

        $productMask = $this->getCompletenessProductMasks->fromValueCollection(
            $entityId,
            $familyCode,
            $valuesWithFullPermissions
        );

        return $productMask->completenessCollectionForProduct($requiredAttributesMasks[$familyCode]);
    }

    /**
     * We can't do a "$entityWithFamily->getRawValues()", because during an update we don't have the last
     * modification of the product in the raw values. To have the good ones we have to make a query.
     */
    private function getRawValues(EntityWithFamilyInterface $entityWithFamily): array
    {
        if ($entityWithFamily instanceof ProductInterface) {
            $rawValues = $this->getRawValues->forProductUuid($entityWithFamily->getUuid());
            if (null === $rawValues) {
                throw new \InvalidArgumentException(
                    sprintf("The raw values of the '%s' product are not found.", $entityWithFamily->getIdentifier())
                );
            }

            return $rawValues;
        } elseif ($entityWithFamily instanceof ProductModelInterface) {
            $rawValues = $this->getRawValues->forProductModelId($entityWithFamily->getId());
            if (null === $rawValues) {
                throw new \InvalidArgumentException(
                    sprintf("The raw values of the '%s' product model are not found.", $entityWithFamily->getCode())
                );
            }

            return $rawValues;
        }

        return $entityWithFamily->getRawValues();
    }
}
