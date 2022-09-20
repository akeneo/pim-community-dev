<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetRecordsActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_get_records_action';

    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_records(): void
    {
        $response = $this->makeCall();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
    }

    public function test_it_returns_errors_if_channel_is_missing(): void
    {
        $response = $this->makeCall(null);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    public function test_it_returns_errors_if_locale_is_missing(): void
    {
        $response = $this->makeCall('ecommerce', null);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    private function makeCall(?string $channel = 'ecommerce', ?string $locale = 'fr_Fr'): Response
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
