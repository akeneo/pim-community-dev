<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Locale Accesses Flat to Standard format converter
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccesses implements ArrayConverterInterface
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
     *      'locale'        => 'en_US',
     *      'view_products' => 'IT support,Manager',
     *      'edit_products' => 'IT support',
     * ]
     *
     * After:
     * [
     *     [
     *         'locale'        => 'en_US',
     *         'user_group'     => 'IT support',
     *         'view_products' => true,
     *         'edit_products' => true,
     *     ], [
     *         'locale'        => 'en_US',
     *         'user_group'     => 'Manager',
     *         'view_products' => true,
     *         'edit_products' => false,
     *     ]
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['locale']);
        $this->fieldChecker->checkFieldsFilling($item, ['locale']);

        $convertedItems = [];
        foreach ($this->getConcernedGroupNames($item) as $groupName) {
            $convertedItems[] = [
                'locale'        => $item['locale'],
                'user_group'    => $groupName,
                'view_products' => in_array($groupName, $this->getGroupNames($item, 'view_products')),
                'edit_products' => in_array($groupName, $this->getGroupNames($item, 'edit_products')),
            ];
        }

        return $convertedItems;
    }

    /**
     * Return all the group concerned by the locale access.
     *
     * @param array $item
     *
     * @return string[]
     */
    protected function getConcernedGroupNames(array $item)
    {
        return array_unique(
            array_merge(
                $this->getGroupNames($item, 'view_products'),
                $this->getGroupNames($item, 'edit_products')
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
