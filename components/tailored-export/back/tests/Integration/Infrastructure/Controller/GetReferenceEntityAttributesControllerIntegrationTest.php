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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredExport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetReferenceEntityAttributesControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_export_get_reference_entity_attributes_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_reference_entity_attributes(): void
    {
        $response = $this->callGetReferenceEntityAttributesRoute('designer');
        $responseContent = json_decode($response->getContent(), true);

        Assert::assertSame([], $responseContent);
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_it_throws_if_reference_entity_code_is_missing(): void
    {
        $response = $this->callGetReferenceEntityAttributesRoute(null);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function callGetReferenceEntityAttributesRoute(?string $referenceEntityCode): Response
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            ['reference_entity_code' => $referenceEntityCode],
        );

        return $this->client->getResponse();
    }
}
