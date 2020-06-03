<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsFieldSetter extends AbstractFieldSetter
{
    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "PACK": {
     *         "products": [
     *              {"identifier": "AKN_TS1", "quantity": 2},
     *              {"identifier": "AKN_TSH2", "quantity": 3},
     *         ],
     *         "product_models": [
     *              {"identifier": "MODEL_AKN_TS1", "quantity": 2},
     *              {"identifier": "MODEL_AKN_TSH2", "quantity": 3},
     *         ],
     *     },
     * }
     *
     * @param EntityWithQuantifiedAssociationsInterface $entity
     */
    public function setFieldData($entity, $field, $data, array $options = [])
    {
        $normalizedQuantifiedAssociations = $entity->normalizeQuantifiedAssociations();
        $mergedQuantifiedAssociations = $this->mergeByKeys($normalizedQuantifiedAssociations, $data);

        $entity->setQuantifiedAssociations(QuantifiedAssociations::createFromNormalized($mergedQuantifiedAssociations));
    }

    public function supportsField($field)
    {
        return 'quantified_associations' === $field;
    }

    /**
     * Merge 2 arrays of QuantifiedAssociations by keys, this is the expected behavior for PATCH partial updates.
     *
     * Given:
     * {
     *     "PRODUCTSET_A": {
     *         "products": [
     *              {"identifier": "AKN_TS1", "quantity": 2}
     *         ],
     *         "product_models": [
     *              {"identifier": "MODEL_AKN_TS1", "quantity": 2}
     *         ],
     *     },
     *     "PRODUCTSET_B": {
     *         "products": [
     *              {"identifier": "AKN_TS1", "quantity": 2}
     *         ],
     *         "product_models": [],
     *     },
     * }
     * When merging:
     * {
     *     "PRODUCTSET_A": {
     *         "products": [
     *              {"identifier": "AKN_TS1_ALT", "quantity": 200}
     *         ],
     *     }
     * }
     * This will result in:
     * {
     *     "PRODUCTSET_A": {
     *         "products": [
     *              {"identifier": "AKN_TS1_ALT", "quantity": 200}
     *         ],
     *         "product_models": [
     *              {"identifier": "MODEL_AKN_TS1", "quantity": 2}
     *         ],
     *     },
     *     "PRODUCTSET_B": {
     *         "products": [
     *              {"identifier": "AKN_TS1", "quantity": 2}
     *         ],
     *         "product_models": [],
     *     },
     * }
     */
    private function mergeByKeys(array $quantifiedAssociations1, array $quantifiedAssociations2): array
    {
        foreach ($quantifiedAssociations2 as $associationTypeCode => $association) {
            if (!isset($quantifiedAssociations1[$associationTypeCode])) {
                $quantifiedAssociations1[$associationTypeCode] = $association;
                continue;
            }

            foreach ($association as $quantifiedLinkType => $quantifiedLinks) {
                $quantifiedAssociations1[$associationTypeCode][$quantifiedLinkType] = $quantifiedLinks;
            }
        }

        return $quantifiedAssociations1;
    }
}
