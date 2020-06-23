<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations;

/**
 * Quantified association field adder
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2°2° Akeneo SAS (http://www.akeneo.com)
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
        $product->getQuantifiedAssociations()->merge(QuantifiedAssociations::createFromNormalized($data));
    }
}
