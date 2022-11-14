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
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class ValidateSearchAndReplaceOperationControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_validate_search_and_replace_operation_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_ok_if_search_and_replace_operation_is_valid(): void
    {
        $response = $this->callValidateSearchAndReplaceOperationRoute([
            [
                'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d7',
                'what' => 'replace me',
                'with' => 'with that',
                'case_sensitive' => false,
            ],
        ]);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
    }

    public function test_it_returns_errors_if_search_and_replace_operation_is_not_valid(): void
    {
        $response = $this->callValidateSearchAndReplaceOperationRoute([
            [
                'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d7',
                'what' => '',
                'with' => 'with that',
                'case_sensitive' => false,
            ],
        ]);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseContent = json_decode($response->getContent(), true);
        Assert::assertSame('This value should not be blank.', $responseContent[0]['messageTemplate']);
        Assert::assertSame('[replacements][ad4e2d5c-2830-4ba8-bf83-07f9935063d7][what]', $responseContent[0]['propertyPath']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function callValidateSearchAndReplaceOperationRoute(array $replacements): Response
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            json_encode([
                'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                'type' => 'search_and_replace',
                'replacements' => $replacements,
            ]),
        );

        return $this->client->getResponse();
    }
}
