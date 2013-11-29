<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\CatalogBundle\Model\Group;

/**
 * Valid group creation (or update) processor
 *
 * Allow to bind input data to an group and validate it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProcessor extends AbstractEntityProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $group = $this->getGroup($item);

        foreach ($item as $key => $value) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $group->setLocale($matches[1]);
                $group->setLabel($value);
            }
        }

        $group->setLocale(null);

        $this->validate($group, $item);

        return $group;
    }

    /**
     * Create an group or get it if already exists
     *
     * @param array $item
     *
     * @return Group
     */
    private function getGroup(array $item)
    {
        $group = $this->findGroup($item['code']);
        if (!$group) {
            $group = new Group();
            $group->setCode($item['code']);

            $groupType = $this->findGroupType($item);
            if ($groupType) {
                $group->setType($groupType);

                if ($group->getType()->isVariant()) {
                    $axis = $this->getAxis($item);
                    $group->setAttributes($axis);
                }
            }
        }

        return $group;
    }

    /**
     * Find group by code
     *
     * @param string $code
     *
     * @return Group|null
     */
    private function findGroup($code)
    {
        return $this
            ->entityManager
            ->getRepository('Pim\Bundle\CatalogBundle\Model\Group')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Find the group type from its code
     *
     * @param array $item
     *
     * @return GroupType null
     */
    private function findGroupType(array $item)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:GroupType')
            ->findOneBy(array('code' => $item['type']));
    }

    /**
     * Get group axis
     *
     * @param array $item
     *
     * @return ProductAttribute[]
     */
    private function getAxis(array $item)
    {
        $attributeCodes = explode(',', $item['attributes']);
        $attributeCodes = array_unique($attributeCodes);
        $attributes = array();

        if (count($attributeCodes) > 0) {
            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->findAttribute($attributeCode);
                if ($attribute) {
                    $attributes[] = $attribute;
                }
            }
        }

        return $attributes;
    }

    /**
     * Find the attribute from its code
     *
     * @param string $code
     *
     * @return ProductAttribute
     */
    private function findAttribute($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }
}
