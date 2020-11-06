<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\ProjectRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\UserRepository;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class UserRepositoryIntegration extends TeamworkAssistantTestCase
{
    public function test_that_it_searches_all_user_that_does_not_belong_to_the_api(): void
    {
        /** @var ConnectionLoader $connectionLoader */
        $connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $erpConnection = $connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, false);
        $connectionLoader->update(
            $erpConnection->code(),
            $erpConnection->label(),
            $erpConnection->flowType(),
            null,
            $this->getUserRoleId(),
            $this->getMarketingGroupId(),
            $erpConnection->auditable()
        );
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'en_US',  'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

        /** @var ProjectRepository $projectRepo */
        $projectRepo = $this->get('pimee_teamwork_assistant.repository.project');
        $project = $projectRepo->findOneByIdentifier($project->getCode());
        die(print_r($project->getProjectStatus()));
$this->destrucs($project->getCode());
        /** @var UserRepository $twaUserRepo */
        $twaUserRepo = $this->get('pimee_teamwork_assistant.repository.user');
        $result = $twaUserRepo->findBySearch(null, ['project' => $project]);
        var_dump($result);
    }

    private function destrucs($code)
    {
        $sqltwa = <<<SQL
SELECT *
FROM pimee_teamwork_assistant_project as project
LEFT JOIN pimee_teamwork_assistant_project_user_group as group ON project.id = group.project_id
SQL;

        $twa = $this->getConnection()->fetchAll($sqltwa, ['code' => $code]);
        var_dump('pimee_teamwork_assistant_project_user_group');
        var_dump($twa);

        $sqltwa = <<<SQL
SELECT *
FROM pimee_teamwork_assistant_project_user_group
SQL;

        $twa = $this->getConnection()->fetchAll($sqltwa);
        var_dump('pimee_teamwork_assistant_project_user_group');
        var_dump($twa);

//        $sqlGroup = <<<SQL
//SELECT *
//FROM oro_access_group
//SQL;
//        $groups = $this->getConnection()->fetchAll($sqlGroup);
//        var_dump('oro_user_group');
//        var_dump($groups);
    }

    private function getMarketingGroupId()
    {
        $sql = <<<SQL
SELECT id
FROM oro_access_group
WHERE name = :name
SQL;

        return (string) $this->getConnection()->fetchColumn($sql, ['name' => 'Marketing']);
    }

    private function getUserRoleId()
    {
        $sql = <<<SQL
SELECT id
FROM oro_access_role
WHERE role = :role
SQL;

        return (string) $this->getConnection()->fetchColumn($sql, ['role' => 'ROLE_USER']);
    }
}
