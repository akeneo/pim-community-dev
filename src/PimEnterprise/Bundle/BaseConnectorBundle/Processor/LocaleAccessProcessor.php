<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor;

/**
 * Locale accesses import processor
 * Allows to bind data into a locale access and validate them
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleAccessProcessor extends TransformerProcessor
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
        $objects = [];

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
     *      'group': 'administrator',
     *      'viewAttributes': true,
     *      'editAttributes': true,
     * ],
     * [
     *      'code': 'general',
     *      'group': 'user',
     *      'viewAttributes': true,
     *      'editAttributes': false,
     * ]
     * @param array $data
     *
     * @return array usable items
     */
    protected function transformDataToItems($data)
    {
        $tmp = [];
        $items = [];

        if (isset($data[self::ACCESS_VIEW])) {
            foreach ($data[self::ACCESS_VIEW] as $group) {
                $tmp[$group][self::ACCESS_VIEW] = true;
            }
            unset($data[self::ACCESS_VIEW]);
        }

        if (isset($data[self::ACCESS_EDIT])) {
            foreach ($data[self::ACCESS_EDIT] as $group) {
                $tmp[$group][self::ACCESS_EDIT] = true;
                $tmp[$group][self::ACCESS_VIEW] = true;
            }
            unset($data[self::ACCESS_EDIT]);
        }

        foreach ($tmp as $group => $accesses) {
            $item = $data;
            $item['userGroup'] = $group;
            $item['viewProducts'] = isset($accesses[self::ACCESS_VIEW]);
            $item['editProducts'] = isset($accesses[self::ACCESS_EDIT]);
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
            'code' => 'locale',
        );
    }
}
