<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;

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
     *              {"uuid": "40a48bff-e241-4aa2-9c06-685da710bd74", "quantity": 3},
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
        $entity->patchQuantifiedAssociations($data);
    }

    public function supportsField($field)
    {
        return 'quantified_associations' === $field;
    }
}
