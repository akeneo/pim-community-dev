<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Collection of prices
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class PriceCollectionType extends CollectionType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_price_collection';
    }
}
