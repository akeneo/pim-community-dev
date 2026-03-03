<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAttributeOptionsControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callRoute('akeneo_identifier_generator_get_attribute_options', [
            'HTTP_X-Requested-With' => 'toto',
        ], [
            'attributeCode' => 'a_simple_select_color',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_returns_http_forbidden_without_the_list_attributes_acl(): void
    {
        $this->setAcls('ROLE_USER', ['action:pim_enrich_attribute_index' => false]);
        $this->loginAs('mary');
        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_attribute_options',
            ['attributeCode' => 'a_simple_select_color', 'codes' => 'familyA1,familyA2'],
        );
        $response = $this->client->getResponse();

        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_http_forbidden_without_the_view_generator_acl(): void
    {
        $this->loginAs('kevin');
        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_attribute_options',
            ['attributeCode' => 'a_simple_select_color', 'codes' => 'familyA1,familyA2'],
        );
        $response = $this->client->getResponse();

        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_a_list_of_attribute_options_filtered_by_codes(): void
    {
        $this->loginAs('Julia');

        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_attribute_options',
            ['attributeCode' => 'a_simple_select_color', 'codes' => ['orange', 'black']]
        );
        $response = $this->client->getResponse();

        $expected = [
            [
                'code' => 'black',
                'labels' => ['en_US' => 'Black'],
            ], [
                'code' => 'orange',
                'labels' => ['en_US' => 'Orange'],
            ],
        ];

        Assert::assertEquals($expected, \json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_returns_a_list_of_attribute_options_with_pagination(): void
    {
        $this->loginAs('Julia');

        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_attribute_options',
            ['attributeCode' => 'a_simple_select_color', 'limit' => 2, 'page' => 1]
        );
        $firstResponse = $this->client->getResponse();

        $expectedFirstPage = [
            [
                'code' => 'black',
                'labels' => ['en_US' => 'Black'],
            ], [
                'code' => 'blue',
                'labels' => ['en_US' => 'Blue'],
            ],
        ];

        Assert::assertEquals($expectedFirstPage, \json_decode($firstResponse->getContent(), true));

        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_attribute_options',
            ['attributeCode' => 'a_simple_select_color', 'limit' => 2, 'page' => 2]
        );
        $response = $this->client->getResponse();

        $expectedSecondPage = [
            [
                'code' => 'brown',
                'labels' => ['en_US' => 'Brown'],
            ], [
                'code' => 'green',
                'labels' => ['en_US' => 'Green'],
            ],
        ];

        Assert::assertEquals($expectedSecondPage, \json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_returns_a_list_of_attribute_options_with_search_params(): void
    {
        $this->loginAs('Julia');

        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_attribute_options',
            ['attributeCode' => 'a_simple_select_color', 'search' => 'w']
        );
        $response = $this->client->getResponse();

        $expected = [
            [
                'code' => 'brown',
                'labels' => ['en_US' => 'Brown'],
            ], [
                'code' => 'white',
                'labels' => ['en_US' => 'White'],
            ], [
                'code' => 'yellow',
                'labels' => ['en_US' => 'Yellow'],
            ],
        ];

        Assert::assertSame($expected, \json_decode($response->getContent(), true));
    }
}
