<?php
namespace Pim\Bundle\ProductBundle\Helper;

use Pim\Bundle\ProductBundle\Entity\ProductSegment;

/**
 * Segment helper
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SegmentHelper
{

    /**
     * Format in array content segment
     *
     * @param ArrayCollection $segments
     *
     * @return array
     * @static
     */
    public static function childrenResponse($segments)
    {
        $return = array();

        foreach ($segments as $segment) {
            $return[] = array(
                'attr' => array(
                    'id' => 'node_'. $segment->getId()
                ),
                'data'  => $segment->getTitle(),
                'state' => static::getState($segment)
            );
        }

        return $return;
    }

    /**
     * Return the state of the segment (leaf if no children, closed otherwise)
     *
     * @param ProductSegment $segment
     *
     * @return string
     * @static
     */
    protected static function getState(ProductSegment $segment)
    {
        return $segment->hasChildren() ? 'closed' : 'leaf';
    }

    /**
     * Format product list
     *
     * @param ArrayCollection $products
     *
     * @return array
     * @static
     */
    public static function productsResponse($products)
    {
        $return = array();

        foreach ($products as $product) {
            $return[] = array(
                'id' => $product->getId(),
                'name' => $product->getSku(),
                'description' => $product->getSku()
            );
        }

        return $return;
    }
}