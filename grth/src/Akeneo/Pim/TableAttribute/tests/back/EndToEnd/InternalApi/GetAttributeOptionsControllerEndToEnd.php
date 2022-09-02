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

namespace Akeneo\Test\Pim\TableAttribute\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Test\Pim\TableAttribute\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeOptionsControllerEndToEnd extends ControllerEndToEndTestCase
{
    private WebClientHelper $webClientHelper;

    /** @test */
    public function it_is_forbidden_when_user_is_not_logged_in(): void
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_attribute_options',
            [
                'tableAttributeCode' => 'nutrition',
                'columnCode' => 'ingredients',
                'selectAttributeCode' => 'toto'
            ],
            'GET'
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_select_options_for_an_attribute_code(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->addAttributeOptions(2);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_attribute_options',
            [
                'selectAttributeCode' => 'a_simple_select'
            ],
            'GET'
        );

        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEqualsCanonicalizing([
            [
                'code' => 'optionA',
                'labels' => [
                    'en_US' => 'Option A',
                ],
            ],
            [
                'code' => 'optionB',
                'labels' => [
                    'en_US' => 'Option B',
                ],
            ],
            [
                'code' => 'option_0',
                'labels' => [
                    'en_US' => 'Option_0',
                    'fr_FR' => 'Optionfr_0',
                ],
            ],
            [
                'code' => 'option_1',
                'labels' => [
                    'en_US' => 'Option_1',
                    'fr_FR' => 'Optionfr_1',
                ],
            ],
        ], \json_decode($response->getContent(), true));
    }

    public function it_returns_20000_options_max(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->addAttributeOptions(20050);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_attribute_options',
            [
                'selectAttributeCode' => 'a_simple_select'
            ],
            'GET'
        );
        $response = $this->client->getResponse();

        Assert::assertCount(20000, \json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_returns_422_when_source_attribute_is_not_a_select(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_attribute_options',
            [
                'selectAttributeCode' => 'a_text',
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function addAttributeOptions(int $count): void
    {
        $attributeOptions = [];
        for ($i=0; $i<$count; $i++) {
            $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                'code' => 'option_'.$i,
                'attribute' => 'a_simple_select',
                'labels' => ['en_US' => 'Option_'.$i, 'fr_FR' => 'Optionfr_'.$i]
            ]);
            $violations = $this->get('validator')->validate($attributeOption);
            Assert::assertCount(0, $violations, \sprintf('The attribute option is not valid: %s', $violations));
            $attributeOptions[] = $attributeOption;
        }
        $this->get('pim_catalog.saver.attribute_option')->saveAll($attributeOptions);
    }
}
