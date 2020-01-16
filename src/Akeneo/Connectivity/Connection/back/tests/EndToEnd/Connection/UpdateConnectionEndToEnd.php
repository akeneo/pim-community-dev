<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateConnectionEndToEnd extends WebTestCase
{
    public function test_it_updates_a_connection(): void
    {
        $connection = $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE);

        $data = [
            "code" => "franklin",
            "label" => "Franklin with updated label",
            "flow_type" => FlowType::DATA_DESTINATION,
            "image" => null,
            "user_role_id" => $connection->userRoleId(),
            "user_group_id" => $connection->userGroupId()
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            '/rest/connections/franklin',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = null;

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_update_a_connection_with_a_bad_request(): void
    {
        $connection = $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE);

        $data = [
            "code" => "wrong_code",
            "label" => "",
            "flow_type" => 'wrong_flow_type',
            "image" => null,
            "user_role_id" => $connection->userRoleId(),
            "user_group_id" => $connection->userGroupId()
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            '/rest/connections/franklin',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [
            "message" => "akeneo_connectivity.connection.constraint_violation_list_exception",
            "errors" => [
                [
                    "name" => "label",
                    "reason" => "akeneo_connectivity.connection.connection.constraint.label.required"
                ],
                [
                    "name" => "flowType",
                    "reason" => "akeneo_connectivity.connection.connection.constraint.flow_type.invalid"
                ]
            ]
        ];

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
