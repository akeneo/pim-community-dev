<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Attribute Group Accesses Flat to Standard format converter
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeGroupAccesses implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /**
     * @param FieldsRequirementChecker $fieldChecker
     */
    public function __construct(FieldsRequirementChecker $fieldChecker)
    {
        $this->fieldChecker = $fieldChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'attribute_group' => 'other',
     *      'view_attributes' => 'IT support,Manager',
     *      'edit_attributes' => 'IT support',
     * ]
     *
     * After:
     * [
     *     [
     *         'attribute_group' => 'other',
     *         'user_group'      => 'IT support',
     *         'view_attributes' => true,
     *         'edit_attributes' => true,
     *     ], [
     *         'attribute_group' => 'other',
     *         'user_group'      => 'Manager',
     *         'view_attributes' => true,
     *         'edit_attributes' => false,
     *     ]
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['attribute_group']);
        $this->fieldChecker->checkFieldsFilling($item, ['attribute_group']);

        $convertedItems = [];
        foreach ($this->getConcernedGroupNames($item) as $groupName) {
            $convertedItems[] = [
                'attribute_group' => $item['attribute_group'],
                'user_group'      => $groupName,
                'view_attributes' => in_array($groupName, $this->getGroupNames($item, 'view_attributes')),
                'edit_attributes' => in_array($groupName, $this->getGroupNames($item, 'edit_attributes')),
            ];
        }

        return $convertedItems;
    }

    /**
     * Return all the group concerned by the attribute group access category access.
     *
     * @param array $item
     *
     * @return string[]
     */
    protected function getConcernedGroupNames(array $item)
    {
        return array_unique(
            array_merge(
                $this->getGroupNames($item, 'view_attributes'),
                $this->getGroupNames($item, 'edit_attributes')
            )
        );
    }

    /**
     * Return the group names of a specific permission.
     *
     * @param array  $item
     * @param string $permission
     *
     * @return string[]
     */
    protected function getGroupNames(array $item, $permission)
    {
        $names = [];
        if (isset($item[$permission]) && '' !== $item[$permission]) {
            $names = explode(',', $item[$permission]);
        }

        return $names;
    }
}
