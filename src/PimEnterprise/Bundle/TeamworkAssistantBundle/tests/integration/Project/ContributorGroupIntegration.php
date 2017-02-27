<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration\Project;

use PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration\TeamworkAssistantTestCase;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

class ContributorGroupIntegration extends TeamworkAssistantTestCase
{
    /**
     * Several contributor groups access to the locale en_US (except Read only)
     */
    public function testToCreateAProjectOnALocaleGrantedToGroups()
    {
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'en_US',  'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

        $this->checkContributorGroup(
            $project,
            ['Marketing', 'Technical Clothing', 'Technical High-Tech', 'Catalog Manager']
        );
    }

    /**
     * The group "All" access to the de_DE locale
     */
    public function testToCreateAProjectOnALocaleGrantedToAll()
    {
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'de_DE',  'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

        $this->checkContributorGroup(
            $project,
            ['Marketing', 'Technical Clothing', 'Technical High-Tech', 'Catalog Manager', 'Read Only']
        );
    }


    /**
     * Marketing and Catalog Manager access to the locale es_ES
     */
    public function testToCreateAProjectOnALocaleGrantedToSpecificGroup()
    {
        $project = $this->createProject('Tshirt - print', 'Julia', 'es_ES', 'tablet', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

        $this->checkContributorGroup($project, ['Marketing', 'Catalog Manager']);
    }

    /**
     * Check the contributor group is well calculated
     *
     * @param ProjectInterface $project
     * @param string[]         $expectedGroup
     */
    private function checkContributorGroup(ProjectInterface $project, array $expectedGroup)
    {
        $sql = <<<SQL
SELECT `group`.`name`
FROM `oro_access_group` AS `group`
INNER JOIN `pimee_teamwork_assistant_project_user_group` AS `project_user_group`
	ON `group`.`id` = `project_user_group`.`user_group_id`
WHERE `project_user_group`.`project_id` = :project_id
SQL;

        $userGroups = $this->getConnection()->fetchAll($sql, [
            'project_id' => $project->getId(),
        ]);

        $diff = array_diff(array_column($userGroups, 'name'), $expectedGroup);

        $this->assertCount(0, $diff);
    }
}
