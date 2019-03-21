<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

class ContributorGroupIntegration extends TeamworkAssistantTestCase
{
    /**
     * Several contributor groups access to the locale en_US (except Read only)
     *
     * @group critical
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
     *
     * @group critical
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
     *
     * @group critical
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
     * The group "All" access to the de_DE locale and has permission to edit the technical attribute group
     *
     * @group critical
     */
    public function testToCreateAProjectWithAnAttributeGroupContainsAllForEditAttributes()
    {
        $attributeGroupRepository = $this->get('pim_catalog.repository.attribute_group');
        $technicalGroup = $attributeGroupRepository->findOneByIdentifier('technical');

        $attributeGroupAccessManager = $this->get('pimee_security.manager.attribute_group_access');
        $attributeGroupAccessManager->setAccess(
            $technicalGroup,
            $this->getUserGroups(['All']),
            $this->getUserGroups(['All'])
        );

        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'de_DE', 'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

        $this->checkContributorGroup($project, ['Catalog Manager', 'Marketing', 'Technical High-Tech', 'All']);
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


    /**
     * @param string[] $groupNames
     *
     * @return array
     */
    private function getUserGroups($groupNames): array
    {
        return array_filter($this->get('pim_user.repository.group')->findAll(), function ($group) use ($groupNames) {
            return in_array($group->getName(), $groupNames);
        });
    }
}
