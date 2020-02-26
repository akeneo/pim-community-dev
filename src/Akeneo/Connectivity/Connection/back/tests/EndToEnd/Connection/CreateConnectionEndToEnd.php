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
class CreateConnectionEndToEnd extends WebTestCase
{
    public function test_it_creates_a_connection(): void
    {
        $data = [
            "code" => "franklin",
            "label" => "Franklin",
            "flow_type" => FlowType::DATA_SOURCE,
            "image" => null,
            "auditable" => false,
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            '/rest/connections',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResultForAutoGeneratedValues = [
            'client_id' => '<client_id>',
            'secret' => '<secret>',
            'username' => '<username>',
            'password' => '<password>',
            'user_role_id' => '<user_role_id>',
            'auditable' => '<auditable>',
        ];
        $expectedResult = array_merge(
            [
                'code' => 'franklin',
                'label' => 'Franklin',
                'flow_type' => FlowType::DATA_SOURCE,
                'image' => null,
                'user_group_id' => null,
                'auditable' => false,
            ],
            $expectedResultForAutoGeneratedValues
        );

        Assert::assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        foreach (array_keys($expectedResult) as $key) {
            Assert::assertArrayHasKey($key, $result);
        }
        Assert::assertEquals($expectedResult, array_merge($result, $expectedResultForAutoGeneratedValues));
    }

    public function test_it_fails_to_create_a_connection_with_a_bad_request(): void
    {
        $data = [
            "code" => "malformed_code_@#$^&*()",
            "label" => "",
            "flow_type" => "wrong_flow_type",
            "image" => null,
            "auditable" => false,
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            '/rest/connections',
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
                    "name" => "code",
                    "reason" => "akeneo_connectivity.connection.connection.constraint.code.invalid"
                ],
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
