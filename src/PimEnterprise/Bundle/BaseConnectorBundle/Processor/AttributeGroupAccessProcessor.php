<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor;

/**
 * Attribute group accesses import processor
 * Allows to bind data into an attribute group access and validate them
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccessProcessor extends TransformerProcessor
{
    /** @staticvar string */
    const ACCESS_VIEW = 'view';

    /** @staticvar string */
    const ACCESS_EDIT = 'edit';

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        $objects = array();

        foreach ($this->transformDataToItems($data) as $item) {
            $objects[] = parent::process($item);
        }

        return $objects;
    }

    /**
     * Transform data to get an array of usable items.
     *
     * IN :
     * [
     *      'code': 'general',
     *      'view': ['administrator', 'user']
     *      'edit': ['administrator']
     * ]
     *
     * OUT :
     * [
     *      'code': 'general',
     *      'role': 'administrator',
     *      'viewAttributes': true,
     *      'editAttributes': true,
     * ],
     * [
     *      'code': 'general',
     *      'role': 'user',
     *      'viewAttributes': true,
     *      'editAttributes': false,
     * ]
     * @param $data
     *
     * @return array usable items
     */
    protected function transformDataToItems($data)
    {
        $tmp = array();
        $items = array();

        if (isset($data[self::ACCESS_VIEW])) {
            foreach ($data[self::ACCESS_VIEW] as $role) {
                $tmp[$role][self::ACCESS_VIEW] = true;
            }
            unset($data[self::ACCESS_VIEW]);
        }

        if (isset($data[self::ACCESS_EDIT])) {
            foreach ($data[self::ACCESS_EDIT] as $role) {
                $tmp[$role][self::ACCESS_EDIT] = true;
                $tmp[$role][self::ACCESS_VIEW] = true;
            }
            unset($data[self::ACCESS_EDIT]);
        }

        foreach ($tmp as $role => $accesses) {
            $item = $data;
            $item['role'] = $role;
            $item['viewAttributes'] = isset($accesses[self::ACCESS_VIEW]);
            $item['editAttributes'] = isset($accesses[self::ACCESS_EDIT]);
            $items[] = $item;
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return array(
            'code' => 'attributeGroup',
        );
    }
}
