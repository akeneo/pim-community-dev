<?php

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetValuesReferenceEntitiesControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->loginAs('Julia');
        $this->callRoute('akeneo_identifier_generator_get_reference_entities_values', [
            'HTTP_X-Requested-With' => 'toto',
        ], ['referenceEntityIdentifier' => 'toto']);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_returns_a_list_of_records_reference_entities_by_identifier(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->loginAs('Julia');

        $this->createReferenceEntity('color', []);
        $this->createRecords('color', ['red', 'blue', 'green']);

        $this->callGetRouteWithQueryParam('akeneo_identifier_generator_get_reference_entities_values', ['referenceEntityIdentifier' => 'color']);
        $response = $this->client->getResponse();

        $expected = [
            [
                'code' => 'blue',
                'labels' => [],
            ],
            [
                'code' => 'green',
                'labels' => [],
            ],
            [
                'code' => 'red',
                'labels' => [],
            ],
        ];

        Assert::assertEquals($expected, \json_decode($response->getContent(), true));
    }
}
