<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\Syndication\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetRecordsControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_syndication_get_records_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_records(): void
    {
        $response = $this->callGetRecordsRoute();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
    }

    public function test_it_returns_errors_if_channel_is_missing(): void
    {
        $response = $this->callGetRecordsRoute(null);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    public function test_it_returns_errors_if_locale_is_missing(): void
    {
        $response = $this->callGetRecordsRoute('ecommerce', null);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function callGetRecordsRoute(?string $channel = 'ecommerce', ?string $locale = 'fr_Fr'): Response
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            ['reference_entity_code' => 'brand'],
            'POST',
            [],
            json_encode([
                'include_codes' => null,
                'exclude_codes' => null,
                'search' => 'alessi',
                'channel' => $channel,
                'locale' => $locale
            ])
        );

        return $this->client->getResponse();
    }
}
