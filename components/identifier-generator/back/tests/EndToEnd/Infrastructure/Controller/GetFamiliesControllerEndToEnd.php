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
final class GetFamiliesControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callRoute('akeneo_identifier_generator_get_families', [
            'HTTP_X-Requested-With' => 'toto',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_returns_http_forbidden_without_list_families_acl(): void
    {
        $this->setAcls('ROLE_USER', ['action:pim_enrich_family_index' => false]);
        $this->loginAs('mary');
        $this->callGetRouteWithQueryParam('akeneo_identifier_generator_get_families', ['codes' => 'familyA1,familyA2']);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_http_forbidden_without_view_generator_acl(): void
    {
        $this->loginAs('kevin');
        $this->callGetRouteWithQueryParam('akeneo_identifier_generator_get_families', ['codes' => 'familyA1,familyA2']);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_a_list_of_families_filtered_by_codes(): void
    {
        $this->loginAs('Julia');

        $this->callGetRouteWithQueryParam('akeneo_identifier_generator_get_families', ['codes' => ['familyA1', 'familyA2']]);
        $response = $this->client->getResponse();

        $expected = [
            [
                'code' => 'familyA1',
                'labels' => [
                    'en_US' => 'A family A1',
                ],
            ],
            [
                'code' => 'familyA2',
                'labels' => [],
            ],
        ];

        Assert::assertEquals($expected, \json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_returns_a_list_of_families_with_pagination(): void
    {
        $this->loginAs('Julia');

        $this->callGetRouteWithQueryParam('akeneo_identifier_generator_get_families', ['limit' => 2, 'page' => 1]);
        $firstResponse = $this->client->getResponse();

        $expectedFirstPage = [
            [
                'code' => 'familyA',
                'labels' => [
                    'fr_FR' => 'Une famille A',
                    'en_US' => 'A family A',
                ],
            ],
            [
                'code' => 'familyA1',
                'labels' => [
                    'en_US' => 'A family A1',
                ],
            ],
        ];

        Assert::assertEquals($expectedFirstPage, \json_decode($firstResponse->getContent(), true));

        $this->callGetRouteWithQueryParam('akeneo_identifier_generator_get_families', ['limit' => 2, 'page' => 2]);
        $response = $this->client->getResponse();

        $expectedSecondPage = [
            [
                'code' => 'familyA2',
                'labels' => [],
            ],
            [
                'code' => 'familyA3',
                'labels' => [],
            ],
        ];

        Assert::assertEquals($expectedSecondPage, \json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_returns_a_list_of_families_with_search_params(): void
    {
        $this->loginAs('Julia');

        $this->callGetRouteWithQueryParam('akeneo_identifier_generator_get_families', ['search' => 'A2']);
        $response = $this->client->getResponse();

        $expected = [
            [
                'code' => 'familyA2',
                'labels' => [],
            ],
        ];

        Assert::assertSame($expected, \json_decode($response->getContent(), true));
    }
}
