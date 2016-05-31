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
 * Job Profile Accesses Flat to Standard format converter
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class JobProfileAccesses implements ArrayConverterInterface
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
     *      'job_profile'         => 'product_import',
     *      'execute_job_profile' => 'IT support,Manager',
     *      'edit_job_profile'    => 'IT support',
     * ]
     *
     * After:
     * [
     *     [
     *         'job_profile'         => 'product_import',
     *         'user_group'          => 'IT support',
     *         'execute_job_profile' => true,
     *         'edit_job_profile'    => true,
     *     ], [
     *         'job_profile'         => 'product_import',
     *         'user_group'          => 'Manager',
     *         'execute_job_profile' => true,
     *         'edit_job_profile'    => false,
     *     ]
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['job_profile']);
        $this->fieldChecker->checkFieldsFilling($item, ['job_profile']);

        $convertedItems = [];
        foreach ($this->getConcernedGroupNames($item) as $groupName) {
            $convertedItems[] = [
                'job_profile'         => $item['job_profile'],
                'user_group'          => $groupName,
                'execute_job_profile' => in_array($groupName, $this->getGroupNames($item, 'execute_job_profile')),
                'edit_job_profile'    => in_array($groupName, $this->getGroupNames($item, 'edit_job_profile')),
            ];
        }

        return $convertedItems;
    }

    /**
     * Return all the group concerned by the job profile access.
     *
     * @param array $item
     *
     * @return string[]
     */
    protected function getConcernedGroupNames(array $item)
    {
        return array_unique(
            array_merge(
                $this->getGroupNames($item, 'execute_job_profile'),
                $this->getGroupNames($item, 'edit_job_profile')
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
