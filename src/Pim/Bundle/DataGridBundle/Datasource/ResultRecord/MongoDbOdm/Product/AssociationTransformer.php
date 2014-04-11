<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Add product association related data
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTransformer
{
    /**
     * @param array                 $result
     * @param string                $associationTypeId
     * @param ProductInterface|null $product
     *
     * @return array
     */
    public function transform(array $result, $associationTypeId, ProductInterface $product = null)
    {
        if ($product) {
            $associationTypeId = (int) $associationTypeId;
            $result['is_associated'] = false;

            $currentAssociation = $product->getAssociations()->filter(
                function ($association) use ($associationTypeId) {
                    return $association->getAssociationType()->getId() === $associationTypeId;
                }
            )->first();

            if ($currentAssociation) {
                $associatedIds = $currentAssociation->getProducts()->map(
                    function ($product) {
                        return $product->getId();
                    }
                )->toArray();

                if (in_array($result['id'], $associatedIds)) {
                    $result['is_associated'] = true;
                }
            }

            $result['is_checked'] = $result['is_associated'];
        }

        return $result;
    }
}
