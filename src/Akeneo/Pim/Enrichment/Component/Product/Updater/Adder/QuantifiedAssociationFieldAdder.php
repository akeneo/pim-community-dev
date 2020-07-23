<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;

/**
 * Quantified association field adder
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationFieldAdder extends AbstractFieldAdder
{
    /**
     * @param array                       $supportedFields
     */
    public function __construct(
        array $supportedFields
    ) {
        $this->supportedFields = $supportedFields;
    }

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
     */
    public function addFieldData($product, $field, $data, array $options = [])
    {
        $quantifiedAssociationsToMerge = QuantifiedAssociationCollection::createFromNormalized($data);

        $product->mergeQuantifiedAssociations($quantifiedAssociationsToMerge);
    }
}
