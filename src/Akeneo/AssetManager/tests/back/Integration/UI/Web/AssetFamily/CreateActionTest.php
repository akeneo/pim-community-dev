<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\UI\Web\AssetFamily;

use Akeneo\AssetManager\Common\Helper\AuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_ASSET_FAMILY_ROUTE = 'akeneo_asset_manager_asset_family_create_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_creates_an_asset_family(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_FAMILY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'code' => 'designer',
                'labels'     => [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
            ]
        );
        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     */
    public function it_creates_an_asset_family_with_no_labels(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_FAMILY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'code' => 'designer',
                'labels' => []
            ]
        );
        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     *
     * @param mixed $invalidIdentifier
     */
    public function it_returns_an_error_when_the_code_is_not_valid(
        $invalidIdentifier,
        string $expectedResponse
    ): void {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_FAMILY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'code' => $invalidIdentifier,
                'labels'     => [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
                'rule_templates' => []
            ]
        );
        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedResponse
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_asset_family_code_is_not_unique()
    {
        $headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'CONTENT_TYPE'          => 'application/json',
        ];
        $content = [
            'code' => 'designer',
            'labels'     => [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            'rule_templates' => []
        ];
        $this->webClientHelper->callRoute($this->client, self::CREATE_ASSET_FAMILY_ROUTE, [], 'POST', $headers, $content);
        $this->webClientHelper->callRoute($this->client, self::CREATE_ASSET_FAMILY_ROUTE, [], 'POST', $headers, $content);

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            '[{"messageTemplate":"pim_asset_manager.asset_family.validation.code.should_be_unique","parameters":{"%asset_family_identifier%":"designer"},"plural":null,"message":"An asset family already exists with code \u0022designer\u0022","root":{"code":"designer","labels":{"fr_FR":"Concepteur","en_US":"Designer"},"ruleTemplates":[]},"propertyPath":"code","invalidValue":{"code":"designer","labels":{"fr_FR":"Concepteur","en_US":"Designer"},"ruleTemplates":[]},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]'
        );
    }

    /**
     * @test
     * @dataProvider invalidLabels
     *
     * @param mixed $invalidLabels
     */
    public function it_returns_an_error_when_the_labels_are_not_valid($invalidLabels, string $expectedResponse): void
    {
        $postContent = [
            'code' => 'designer',
            'rule_templates' => []
        ];
        $postContent = array_merge($postContent, $invalidLabels);

        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_FAMILY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );
        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedResponse
        );
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_FAMILY_ROUTE,
            [
                'code' => 'celine_dion',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeCreationRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_FAMILY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => 'designer',
                'labels'     => [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_family_create', true);

        $findActivatedLocalesByIdentifiers = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    public function invalidIdentifiers()
    {
        return [
            'Identifier has a dash character' => [
                'invalid-code',
                '[{"messageTemplate":"pim_asset_manager.asset_family.validation.code.pattern","parameters":{"{{ value }}":"\u0022invalid-code\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"code":"invalid-code","labels":{"fr_FR":"Concepteur","en_US":"Designer"},"ruleTemplates":[]},"propertyPath":"code","invalidValue":"invalid-code","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Identifier is 256 characters'    => [
                str_repeat('a', 256),
                '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"code":"aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa","labels":{"fr_FR":"Concepteur","en_US":"Designer"},"ruleTemplates":[]},"propertyPath":"code","invalidValue":"aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
        ];
    }

    public function invalidLabels()
    {
        return [
            'label as an integer'           => [
                ['labels' => ['fr_FR' => 1]],
                '[{"messageTemplate":"invalid label for locale code \u0022fr_FR\u0022: This value should be of type string., \u00221\u0022 given","parameters":{"{{ value }}":"1","{{ type }}":"string"},"plural":null,"message":"invalid label for locale code \u0022fr_FR\u0022: This value should be of type string., \u00221\u0022 given","root":{"code":"designer","labels":{"fr_FR":1},"ruleTemplates":[]},"propertyPath":"labels","invalidValue":{"fr_FR":1},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'The locale code as an integer' => [
                ['labels' => [1 => 'Designer']],
                '[{"messageTemplate":"invalid locale code: This value should be of type string.","parameters":{"{{ value }}":"1","{{ type }}":"string"},"plural":null,"message":"invalid locale code: This value should be of type string.","root":{"code":"designer","labels":{"1":"Designer"},"ruleTemplates":[]},"propertyPath":"labels","invalidValue":{"1":"Designer"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
        ];
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_family_create', false);
    }
}
