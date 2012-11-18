<?php
namespace Pim\Bundle\CatalogBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Bap\Bundle\FlexibleEntityBundle\Model\EntitySet as ProductSet;

/**
 * Aims to transform array to product set and reverse operation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSetToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ProductManager
     */
    private $pm;

    /**
     * @param ProductManager $om
     */
    public function __construct(ProductManager $pm)
    {
        $this->pm = $pm;
    }

    /**
     * Transforms an object (product set) to a array.
     *
     * @param ProductSet $set
     * @return array
     */
    public function transform($set)
    {
        $data = array();
        // base data
        $data['id']=    $set->getId();
        $data['code']=  $set->getCode();
        $data['title']= $set->getTitle();
        // groups
        $data['groups']= array();
        foreach ($set->getGroups() as $group) {
            if (!$group->getCode()) {
                throw new TransformationFailedException(sprintf('A group of set "%s" has no code !', $set->getId()));
            }
            $groupData = array();
            $groupData['id']=    $group->getId();
            $groupData['code']=  $group->getCode();
            $groupData['title']= $group->getTitle();
            // attributes
            $groupData['attributes']= array();
            /**
            foreach ($group->getAttributes() as $attribute) {
                if (!$attribute->getCode()) {
                    throw new TransformationFailedException(sprintf('An attribute of set "%s" has no code !', $set->getId()));
                }
                $attributeData = array();
                $attributeData['id']=    $attribute->getId();
                $attributeData['code']=  $attribute->getCode();
                $attributeData['title']= $attribute->getTitle();
                $groupData['attributes'][$attribute->getCode()]= $attributeData;
            }*/
            $data['groups'][$group->getCode()]= $groupData;
        }

        return $data;
    }

    /**
     * Transforms a array to an object (product set).
     *
     * @param array                         $data
     * @return ProductSet
     * @throws TransformationFailedException if object (set) is not found.
     */
    public function reverseTransform($data)
    {
        // get or create set
        $setId = $data['id'];
        $set = null;
        if ($setId) {
            $set = $this->pm->getSetRepository()->find($setId);
        }
        if (!$set) {
            $set = $this->pm->getNewSetInstance();
        }
        // TODO
        return $set;
    }
}
