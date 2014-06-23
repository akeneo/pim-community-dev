<?php

namespace Pim\Bundle\TransformBundle\Transformer\MongoDB;

use Pim\Bundle\TransformBundle\Transformer\ObjectTransformerInterface;

use Pim\Bundle\CatalogBundle\Model\ProductPrice;

use MongoId;

/**
 * Transform a product price into a MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceTransformer implements ObjectTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($object, array $context = [])
    {
        $doc = new \StdClass();
        $doc->_id = new MongoId;
        $doc->currency = $object->getCurrency();
        if (null !== $object->getData()) {
            $doc->data = $object->getData();
        }

        return $doc;
    }
}
