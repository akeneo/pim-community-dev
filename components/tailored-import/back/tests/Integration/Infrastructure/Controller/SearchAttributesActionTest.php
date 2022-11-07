<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Component\HttpFoundation\Response;

class SearchAttributesActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_search_attributes_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_can_search_provided_attributes_based_on_a_search_value_and_locale(): void
    {
        $attributeCodes = [
            'a_metric',
            'a_number',
            'a_price',
            'a_simple_select',
            'a_multi_select',
        ];
        $search = 'select';
        $localeCode = 'en_US';

        $expected = [
            'a_simple_select',
            'a_multi_select',
        ];

        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            json_encode([
                'attribute_codes' => $attributeCodes,
                'locale_code' => $localeCode,
                'search' => $search,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $matchingAttributeCodes = \json_decode($response->getContent(), true);
        $this->assertSame($expected, $matchingAttributeCodes);
    }

    public function test_it_returns_a_bad_request_when_attribute_codes_are_missing(): void
    {
        $search = 'select';
        $localeCode = 'en_US';

        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            json_encode([
                'locale_code' => $localeCode,
                'search' => $search,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
