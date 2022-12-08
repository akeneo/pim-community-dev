<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateIdentifierGeneratorControllerEndToEnd extends ControllerEndToEndTestCase
{
    private const VALID_IDENTIFIER = [
        'labels' => [
            'en_US' => 'My new generator',
            'fr_FR' => 'Mon nouveau générateur',
        ],
        'target' => 'sku',
        'conditions' => [],
        'structure' => [[
            'type' => 'free_text',
            'string' => 'AKN',
        ]],
        'delimiter' => null,
    ];

    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_rest_update',
            [
                'code' => 'my_new_generator',
            ],
            [
                'HTTP_X-Requested-With' => 'toto',
            ]
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_update_identifier(): void
    {
        $this->loginAs('Julia');
        $this->insertDefaultIdentifierGenerator();

        $updateGenerator = self::VALID_IDENTIFIER;
        $updateGenerator['delimiter'] = '-';
        $updateGenerator['structure'] = [
            [
                'type' => 'free_text',
                'string' => 'AKN',
            ],
            [
                'type' => 'auto_number',
                'numberMin' => 3,
                'digitsMin' => 2,
            ],
        ];
        $updateGenerator['labels'] =  [
            'en_US' => 'My generator updated',
            'fr_FR' => 'Mon générateur modifié',
        ];

        $this->callUpdateRoute(
            'akeneo_identifier_generator_rest_update',
            ['code' => 'my_new_generator'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($updateGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $uuid = $this->getUuidFromCode('my_new_generator');
        Assert::assertSame(
            sprintf(
                '{"uuid":"%s","code":"my_new_generator","conditions":[],"structure":[{"type":"free_text","string":"AKN"},{"type":"auto_number","numberMin":3,"digitsMin":2}],"labels":{"en_US":"My generator updated","fr_FR":"Mon g\u00e9n\u00e9rateur modifi\u00e9"},"target":"sku","delimiter":"-"}',
                $uuid
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_not_update_an_invalid_generator(): void
    {
        $this->loginAs('Julia');
        $this->insertDefaultIdentifierGenerator();

        $updateGenerator = self::VALID_IDENTIFIER;
        $updateGenerator['target'] = 'unknown';

        $this->callUpdateRoute(
            'akeneo_identifier_generator_rest_update',
            ['code' => 'my_new_generator'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($updateGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertSame(
            '[{"path":"target","message":"The \u0022unknown\u0022 attribute code given as target does not exist"}]',
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_not_update_not_found_generator(): void
    {
        $this->loginAs('Julia');
        $this->insertDefaultIdentifierGenerator();

        $updateGenerator = self::VALID_IDENTIFIER;
        $updateGenerator['delimiter'] = '-';

        $this->callUpdateRoute(
            'akeneo_identifier_generator_rest_update',
            ['code' => 'unknown_generator'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($updateGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertSame(
            '[{"path":"","message":"Identifier generator \u0022unknown_generator\u0022 does not exist or you do not have permission to access it."}]',
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_not_work_with_invalid_json(): void
    {
        $this->loginAs('Julia');
        $this->insertDefaultIdentifierGenerator();

        $this->callUpdateRoute(
            'akeneo_identifier_generator_rest_update',
            ['code' => 'my_new_generator'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            '[an invalid { json',
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /** @test */
    public function it_should_not_update_an_incomplete_generator(): void
    {
        $this->loginAs('Julia');
        $this->insertDefaultIdentifierGenerator();

        $incompleteGenerator = self::VALID_IDENTIFIER;
        unset($incompleteGenerator['target']);

        $this->callUpdateRoute(
            'akeneo_identifier_generator_rest_update',
            ['code' => 'my_new_generator'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($incompleteGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertSame(
            '[{"message":"Expected the key \u0022target\u0022 to exist."}]',
            $response->getContent()
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }

    private function insertDefaultIdentifierGenerator(): void
    {
        $defaultIdentifierGenerator = self::VALID_IDENTIFIER;
        $defaultIdentifierGenerator['code'] = 'my_new_generator';

        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($defaultIdentifierGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    private function getUuidFromCode(string $code): string
    {
        return $this->get('database_connection')->executeQuery(<<<SQL
SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_identifier_generator WHERE code=:code
SQL, ['code' => $code])->fetchOne();
    }
}
