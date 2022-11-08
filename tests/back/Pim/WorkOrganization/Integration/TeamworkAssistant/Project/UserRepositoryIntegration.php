<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\ProjectRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

class UserRepositoryIntegration extends TeamworkAssistantTestCase
{
    public function test_that_it_searches_all_users_that_do_not_belong_to_the_api(): void
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
        $connectionUsername = $erpConnection->username();
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'en_US', 'ecommerce', [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['tshirt'],
            ],
        ]);

        /** @var UnitOfWorkAndRepositoriesClearer $clearer */
        $clearer = $this->get('pim_connector.doctrine.cache_clearer');
        $clearer->clear();

        /** @var ProjectRepository $projectRepo */
        $projectRepo = $this->get('pimee_teamwork_assistant.repository.project');
        $project = $projectRepo->findOneByIdentifier($project->getCode());

        /** @var UserRepository $twaUserRepo */
        $twaUserRepo = $this->get('pimee_teamwork_assistant.repository.user');
        $resultUsers = $twaUserRepo->findBySearch(null, ['project' => $project]);
        $resultUsernames = array_map(function (User $user) {
            return $user->getUsername();
        }, $resultUsers);

        $this->assertNotContains($connectionUsername, $resultUsernames);
    }

    private function getMarketingGroupId(): string
    {
        $sql = <<<SQL
SELECT id
FROM oro_access_group
WHERE name = :name
SQL;

        return (string)$this->getConnection()->fetchColumn($sql, ['name' => 'Marketing']);
    }

    private function getUserRoleId(): string
    {
        $sql = <<<SQL
SELECT id
FROM oro_access_role
WHERE role = :role
SQL;

        return (string)$this->getConnection()->fetchColumn($sql, ['role' => 'ROLE_USER']);
    }
}
