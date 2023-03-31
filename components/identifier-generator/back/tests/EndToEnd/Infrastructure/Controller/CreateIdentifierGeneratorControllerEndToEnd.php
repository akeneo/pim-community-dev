<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class CreateIdentifierGeneratorControllerEndToEnd extends ControllerEndToEndTestCase
{
    private const VALID_IDENTIFIER = [
        'code' => 'my_new_generator',
        'labels' => [
            'en_US' => 'My new generator',
            'fr_FR' => 'Mon nouveau générateur',
        ],
        'target' => 'sku',
        'conditions' => [],
        'structure' => [
            [
                'type' => 'free_text',
                'string' => 'AKN',
            ],
        ],
        'delimiter' => null,
        'text_transformation' => 'no',
    ];

    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callCreateRoute('akeneo_identifier_generator_rest_create', [
            'HTTP_X-Requested-With' => 'toto',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_return_http_forbidden_without_manage_permission(): void
    {
        $this->loginAs('mary');
        $this->callCreateRoute('akeneo_identifier_generator_rest_create');
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_should_create_a_generator(): void
    {
        $this->loginAs('Julia');
        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode(self::VALID_IDENTIFIER),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $uuid = $this->getUuidFromCode('my_new_generator');
        Assert::assertSame(
            \sprintf(
                '{"uuid":"%s","code":"my_new_generator","conditions":[],"structure":[{"type":"free_text","string":"AKN"}],"labels":{"en_US":"My new generator","fr_FR":"Mon nouveau g\u00e9n\u00e9rateur"},"target":"sku","delimiter":null,"text_transformation":"no"}',
                $uuid
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_not_create_an_incomplete_generator(): void
    {
        $this->loginAs('Julia');
        $incompleteGenerator = self::VALID_IDENTIFIER;
        unset($incompleteGenerator['code']);
        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($incompleteGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertSame(
            '[{"path":"code","message":"This value should not be blank."}]',
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_not_create_an_invalid_generator(): void
    {
        $this->loginAs('Julia');
        $invalidGenerator = self::VALID_IDENTIFIER;
        $invalidGenerator['structure'][] = ['type' => 'unknown_type'];
        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($invalidGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertSame(
            '[{"path":"structure[1][type]","message":"Type \u0022unknown_type\u0022 can only be one of the following: \u0022free_text\u0022, \u0022auto_number\u0022, \u0022family\u0022, \u0022simple_select\u0022, \u0022reference_entity\u0022."}]',
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_not_work_with_invalid_json(): void
    {
        $this->loginAs('Julia');
        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            '[an invalid { json',
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    private function getUuidFromCode(string $code): string
    {
        return $this->get('database_connection')->executeQuery(<<<SQL
SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_identifier_generator WHERE code=:code
SQL, ['code' => $code])->fetchOne();
    }
}
