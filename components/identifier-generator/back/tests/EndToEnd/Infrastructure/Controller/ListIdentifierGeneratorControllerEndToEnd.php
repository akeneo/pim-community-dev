<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class ListIdentifierGeneratorControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callRoute('akeneo_identifier_generator_rest_list', [
            'HTTP_X-Requested-With' => 'toto',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_get_all_identifiers(): void
    {
        $this->loginAs('Julia');

        $expectedGenerator = [
            'code' => 'my_new_generator',
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
            'text_transformation' => 'no',
        ];

        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($expectedGenerator),
        );

        $this->callRoute('akeneo_identifier_generator_rest_list');
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $result = array_map(function ($ig) {
            unset($ig['uuid']);

            return $ig;
        }, json_decode($response->getContent(), true));

        Assert::assertEquals([$expectedGenerator], $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }
}
