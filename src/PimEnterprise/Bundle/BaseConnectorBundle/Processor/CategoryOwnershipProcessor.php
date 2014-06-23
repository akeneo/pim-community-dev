<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor;

/**
 * Category ownership import processor
 * Allows to bind data into a category ownership and validate them
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryOwnershipProcessor extends TransformerProcessor
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
     *      'roles': ['administrator', 'user']
     * ]
     *
     * OUT :
     * [
     *      'code': 'general',
     *      'role': 'administrator',
     * ],
     * [
     *      'code': 'general',
     *      'role': 'user',
     * ]
     * @param array $data
     *
     * @return array usable items
     */
    protected function transformDataToItems($data)
    {
        $items = [];
        $category = $data['code'];
        foreach ($data['roles'] as $role) {
            $items[]= [
                'code' => $category,
                'role' => $role
            ];
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return array(
            'code' => 'category',
        );
    }
}
