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

use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Asset Category Accesses Flat to Standard format converter
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AssetCategoryAccesses implements ArrayConverterInterface
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
     *      'category'   => 'videos',
     *      'view_items' => 'IT support,Manager',
     *      'edit_items' => 'IT support',
     *      'own_items'  => '',
     * ]
     *
     * After:
     * [
     *     [
     *         'category'   => 'videos',
     *         'user_group'  => 'IT support',
     *         'view_items' => true,
     *         'edit_items' => true,
     *         'own_items'  => false,
     *     ], [
     *         'category'   => 'videos',
     *         'user_group'  => 'Manager',
     *         'view_items' => true,
     *         'edit_items' => false,
     *         'own_items'  => false,
     *     ]
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['category']);
        $this->fieldChecker->checkFieldsFilling($item, ['category']);

        $convertedItems = [];
        foreach ($this->getConcernedGroupNames($item) as $groupName) {
            $convertedItems[] = [
                'category'   => $item['category'],
                'user_group' => $groupName,
                'view_items' => in_array($groupName, $this->getGroupNames($item, 'view_items')),
                'edit_items' => in_array($groupName, $this->getGroupNames($item, 'edit_items')),
                'own_items'  => in_array($groupName, $this->getGroupNames($item, 'own_items')),
            ];
        }

        return $convertedItems;
    }

    /**
     * Return all the group concerned by the asset category access.
     *
     * @param array $item
     *
     * @return string[]
     */
    protected function getConcernedGroupNames(array $item)
    {
        return array_unique(
            array_merge(
                $this->getGroupNames($item, 'view_items'),
                $this->getGroupNames($item, 'edit_items'),
                $this->getGroupNames($item, 'own_items')
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
