<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This class merge the quantified associations of a list of entities, this is used for inherited associations
 * between product models and product variants.
 *
 * Given an array of entities, eg [product_model_1, product_variant_1, product_variant_2],
 * the returned values will be an array of normalized quantified associations from all this entities.
 * When the same association is defined at different levels, the quantity in the child will override
 * the one of the parent.
 */
class QuantifiedAssociationsMerger
{
    public function normalizeAndMergeQuantifiedAssociationsFrom(array $entitiesWithQuantifiedAssociations): array
    {
        if (empty($entitiesWithQuantifiedAssociations)) {
            return [];
        }

        $firstEntityWithQuantifiedAssociations = array_shift($entitiesWithQuantifiedAssociations);
        $mergedQuantifiedAssociations = $firstEntityWithQuantifiedAssociations->getQuantifiedAssociations();
        foreach ($entitiesWithQuantifiedAssociations as $entity) {
            if (!$entity instanceof EntityWithQuantifiedAssociationsInterface) {
                continue;
            }

            $mergedQuantifiedAssociations = $mergedQuantifiedAssociations->merge($entity->getQuantifiedAssociations());
        }

        return $mergedQuantifiedAssociations->normalize();
    }
}
