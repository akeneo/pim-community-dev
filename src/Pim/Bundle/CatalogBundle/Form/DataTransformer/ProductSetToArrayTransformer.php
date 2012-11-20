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
     * @param ProductManager $pm
     */
    public function __construct(ProductManager $pm)
    {
        $this->pm = $pm;
    }

    /**
     * Transforms an object (product set) to a array.
     *
     * @param ProductSet $set
     * 
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
            foreach ($group->getAttributes() as $attribute) {
                $groupData['attributes'][]= $attribute->getId();
            }
            $data['groups'][$group->getCode()]= $groupData;
        }

        return $data;
    }

    /**
     * Transforms a array to an object (product set).
     *
     * @param array                         $data
     * 
     * @return ProductSet
     * 
     * @throws TransformationFailedException if object (set) is not found.
     */
    public function reverseTransform($data)
    {
        // get or create set
        $setId = $data['id'];
        $entity = null;
        if ($setId) {
            $entity = $this->pm->getSetRepository()->find($setId);
        }
        if (!$entity) {
            $entity = $this->pm->getNewSetInstance();
        }

        // set general set information
        $entity->setCode($data['code']);
        $entity->setTitle($data['title']);

        // create new groups
        $groupsUpdate = array();
        $groupsNew = array();
        foreach ($data['groups'] as $groupCode => $groupData) {

            // add new group
            if ($groupData['id'] == '') {
                // add group
                $groupsNew[]= $groupData;
                $newGroup = $this->pm->getNewGroupInstance();
                $newGroup->setCode($groupData['code']);
                $newGroup->setTitle($groupData['title']);
                $entity->addGroup($newGroup);

                // add attributes in new group
                if (isset($groupData['attributes'])) {
                    foreach ($groupData['attributes'] as $attId) {
                        $attribute = $this->pm->getAttributeRepository()->find($attId);
                        $newGroup->addAttribute($attribute);
                    }
                }

                // group to update
            } else {
                $groupsUpdate[$groupData['id']]= $groupData;
            }
        }

        // update existing groups
        foreach ($entity->getGroups() as $group) {
            // delete if not a new one and not in updated
            if ($group->getId() and !in_array($group->getId(), array_keys($groupsUpdate))) {
                $entity->removeGroup($group);
                // update each attribute
            } else {
                // prepare attribute ids
                $attributesUpdate = isset($groupsUpdate[$group->getId()]['attributes']) ? $groupsUpdate[$group->getId()]['attributes'] : array();
                // delete moved attributes
                if ($group->getId()) {
                    foreach ($group->getAttributes() as $attribute) {
                        // delete
                        if (!in_array($attribute->getId(), $attributesUpdate)) {
                            $group->removeAttribute($attribute);
                        }
                    }
                }
                // add new attributes
                foreach ($attributesUpdate as $attId) {
                    $attribute = $this->pm->getAttributeRepository()->find($attId);
                    if (!$group->getAttributes()->contains($attribute)) {
                        $group->addAttribute($attribute);
                    }
                }
            }
        }

        return $entity;
    }
}
