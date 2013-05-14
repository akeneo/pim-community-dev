<?php
namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\SegmentationTreeBundle\Manager\SegmentManager;

use Pim\Bundle\ProductBundle\Entity\ProductSegment;

/**
 * Extends SegmentManager for classification tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentManager extends SegmentManager
{

    /**
     * Get a new tree instance
     *
     * @return ProductSegment
     */
    public function getTreeInstance()
    {
        $tree = $this->getSegmentInstance();
        $tree->setParent(null);

//         $unclassifiedNode = $this->getSegmentInstance();
//         $unclassifiedNode->setParent($tree);
//         $unclassifiedNode->setIsDynamic(true);
//         $unclassifiedNode->setCode('unclassified-node');
//         $unclassifiedNode->setTitle('Unclassified Node');

//         $tree->addChild($unclassifiedNode);

        return $tree;
    }
}
