<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor;

/**
 * Allows to bind data into an access and validate them
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
abstract class AbstractAccessProcessor extends TransformerProcessor
{
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
     *      'permission1': ['group A', 'group B']
     *      'permission2': ['group A']
     * ]
     *
     * OUT :
     * [
     *      'code': 'general',
     *      'userGroup': 'group A',
     *      'permission1': true,
     *      'permission2': true,
     * ],
     * [
     *      'code': 'general',
     *      'userGroup': 'group B',
     *      'permission1': true,
     * ]
     * @param array $data
     *
     * @return array usable items
     */
    protected function transformDataToItems($data)
    {
        $items = [];

        foreach ($data as $permission => $groups) {
            if (is_array($groups)) {
                foreach ($groups as $group) {
                    $items[$group][$permission] = true;
                }
            }
        }

        foreach ($items as $group => $accesses) {
            $accesses = array_merge($this->getPermissions(), $accesses);
            $accesses['userGroup'] = $group;
            if (isset($data['code'])) {
                $accesses['code'] = $data['code'];
            }
            $items[$group] = $accesses;
        }

        return $items;
    }

    /**
     * @return array
     */
    abstract protected function getSupportedPermissions();

    /**
     * @return array
     */
    protected function getPermissions()
    {
        return array_fill_keys($this->getSupportedPermissions(), false);
    }
}
