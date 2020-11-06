<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\TeamworkAssistant;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class FindContributorsEndToEnd extends TeamworkAssistantWebTestCase
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

        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            sprintf('/project/%s/contributors', $project->getId()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        // 
        die(var_dump($result));

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }


    private function getMarketingGroupId()
    {
        $sql = <<<SQL
SELECT id
FROM oro_access_group
WHERE name = :name
SQL;

        $toto = $this->connection->fetchColumn($sql, ['name' => 'Marketing']);
        var_dump('marketing id');
        var_dump($toto);
        return (string) $toto;
    }

    private function getUserRoleId()
    {
        $sql = <<<SQL
SELECT id
FROM oro_access_role
WHERE role = :role
SQL;
        $toto = $this->connection->fetchColumn($sql, ['role' => 'ROLE_USER']);
        var_dump('user role id');
        var_dump($toto);
        return (string) $toto;
    }
}
