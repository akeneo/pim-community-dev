<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this target code.
 */

namespace Akeneo\Pim\Structure\FamilyTemplate\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetFamilyTemplatesActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pim_family_templates_get_action';

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    /**
     * @test
     */
    public function it_returns_family_templates()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'GET',
            []
        );

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
