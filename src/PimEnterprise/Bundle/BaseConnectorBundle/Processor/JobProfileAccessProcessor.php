<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor;

/**
 * Job profile access import processor
 * Allows to bind data into a job profile access and validate them
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobProfileAccessProcessor extends TransformerProcessor
{
    /** @staticvar string */
    const ACCESS_EXECUTE = 'execute';

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
     *      'execute': ['administrator', 'user']
     *      'edit': ['administrator']
     * ]
     *
     * OUT :
     * [
     *      'code': 'general',
     *      'group': 'administrator',
     *      'executeJobProfile': true,
     *      'editJobProfile': true,
     * ],
     * [
     *      'code': 'general',
     *      'group': 'user',
     *      'executeJobProfile': true,
     *      'editJobProfile': false,
     * ]
     * @param array $data
     *
     * @return array usable items
     */
    protected function transformDataToItems($data)
    {
        $tmp = [];
        $items = [];

        if (isset($data[self::ACCESS_EXECUTE])) {
            foreach ($data[self::ACCESS_EXECUTE] as $group) {
                $tmp[$group][self::ACCESS_EXECUTE] = true;
            }
            unset($data[self::ACCESS_EXECUTE]);
        }

        if (isset($data[self::ACCESS_EDIT])) {
            foreach ($data[self::ACCESS_EDIT] as $group) {
                $tmp[$group][self::ACCESS_EDIT] = true;
                $tmp[$group][self::ACCESS_EXECUTE] = true;
            }
            unset($data[self::ACCESS_EDIT]);
        }

        foreach ($tmp as $group => $accesses) {
            $item = $data;
            $item['userGroup'] = $group;
            $item['executeJobProfile'] = isset($accesses[self::ACCESS_EXECUTE]);
            $item['editJobProfile'] = isset($accesses[self::ACCESS_EDIT]);
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
            'code' => 'jobProfile',
        );
    }
}
