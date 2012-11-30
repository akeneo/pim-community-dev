<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Helper;
/**
 * Helper for Tree Controller to format Category in JSON content
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsonTreeHelper
{
    /**
     * Format content for node creation response
     * @param integer $status     response status value
     * @param integer $categoryId category id
     *
     * @return array
     * @static
     */
    public static function createNodeResponse($status, $categoryId)
    {
        return array('status' => 1, 'id' => $categoryId);
    }

    /**
     * Format in array content category for JSON response
     * @param ArrayCollection $categories
     *
     * @return array
     * @static
     */
    public static function childrenResponse($categories)
    {
        $return = array();

        foreach ($categories as $category) {
            $return[] = array(
                'attr' => array('id' => 'node_'. $category->getId(), 'rel' => $category->getType()),
                'data' => $category->getTitle(),
                'state'=> 'closed'
            );
        }

        return $return;
    }

    /**
     * Format in array content for JSON search response
     * @param ArrayCollection $categories
     *
     * @return array
     * @static
     */
    public static function searchResponse($categories)
    {
        $return = array();

        foreach ($categories as $category) {
            $return[] = '#node_'. $category->getId();
        }

        return $return;
    }

    /**
     * Return a status OK
     * @return array
     * @static
     */
    public static function statusOKResponse()
    {
        return array('status' => 1);
    }
}