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

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindFileDataByFileKey;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_ASSET_ROUTE = 'akeneo_asset_manager_asset_create_rest';

    /** @var FixturesLoader */
    private $fixturesLoader;

    /** @var InMemoryFileExists */
    private $fileExists;

    /** @var InMemoryFindFileDataByFileKey */
    private $findFileData;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->fixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $this->fileExists = $this->get('akeneo_assetmanager.infrastructure.persistence.query.file_exists');
        $this->findFileData = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_file_data_by_file_key');

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_creates_an_asset(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_ROUTE,
            [
                'assetFamilyIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'asset_family_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'values' => [],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
        $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset_indexer')->assertIndexRefreshed();
    }

    /**
     * @test
     */
    public function it_creates_a_asset_with_no_label(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_ROUTE,
            [
                'assetFamilyIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'code' => 'intel',
                'asset_family_identifier' => 'brand',
                'values' => [],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
        $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset_indexer')->assertIndexRefreshed();
    }

    /**
     * @test
     */
    public function it_creates_an_asset_with_values(): void
    {
        $this->fileExists->save('/a/b/c/philou.png');
        $fileData = [
            'originalFilename' => 'philou.png',
            'filePath' => '/a/b/c/philou.png',
            'size' => 1000,
            'mimeType' => 'image/png',
            'extension' => 'png',
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ];
        $this->findFileData->save($fileData);

        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_ROUTE,
            [
                'assetFamilyIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'asset_family_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'values' => [
                    [
                        "attribute" => "logo_brand_fingerprint",
                        "channel" => null,
                        "locale" => null,
                        "data" => [
                          "filePath" => "/a/b/c/philou.png",
                          "originalFilename" => "philou.png",
                          "size" => 5396,
                          "mimeType" => "image/png",
                          "extension" => "png"
                        ]
                    ]
                ],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
        $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset_indexer')->assertIndexRefreshed();
    }

    /**
     * @test
     */
    public function it_creates_an_asset_with_invalid_values(): void
    {
        $this->fileExists->save('/a/b/c/philou.png');

        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_ROUTE,
            [
                'assetFamilyIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'asset_family_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'values' => [
                    [
                        "attribute" => "logo_brand_fingerprint",
                        "channel" => null,
                        "locale" => null,
                        "data" => [
                          "filePath" => "INVALID_FILE_PATH",
                          "originalFilename" => "INVALID_FILE_NAME",
                          "size" => 5396,
                          "mimeType" => "image/png",
                          "extension" => "png"
                        ]
                    ]
                ],
            ]
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $errorMessage = json_decode($response->getContent(), true)[0]['messageTemplate'];
        Assert::assertEquals('The file "INVALID_FILE_PATH" was not found.', $errorMessage);
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     *
     * @param mixed $assetCode
     * @param mixed $assetFamilyIdentifier
     * @param mixed $assetFamilyIdentifierURL
     */
    public function it_returns_an_error_when_the_asset_identifier_is_not_valid(
        $assetCode,
        $assetFamilyIdentifier,
        $assetFamilyIdentifierURL,
        string $expectedResponse
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_ROUTE,
            [
                'assetFamilyIdentifier' => $assetFamilyIdentifierURL,
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'asset_family_identifier' => $assetFamilyIdentifier,
                'code' => $assetCode,
                'labels' => [],
                'values' => [],
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
    public function it_returns_an_error_when_the_asset_identifier_is_not_unique()
    {
        $urlParameters = ['assetFamilyIdentifier' => 'designer'];
        $headers = ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'];
        $content = [
            'identifier'                 => 'designer_starck_1',
            'asset_family_identifier' => 'designer',
            'code'                       => 'starck',
            'labels'                     => ['fr_FR' => 'Philippe Starck'],

        ];
        $method = 'POST';
        $this->webClientHelper->callRoute($this->client, self::CREATE_ASSET_ROUTE, $urlParameters, $method, $headers,
            $content);
        $this->webClientHelper->callRoute($this->client, self::CREATE_ASSET_ROUTE, $urlParameters, $method, $headers,
            $content);
        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            '[{"messageTemplate":"pim_asset_manager.asset.validation.code.should_be_unique","parameters":{"%code%":[]},"plural":null,"message":"An asset already exists with code \u0022starck\u0022","root":{"assetFamilyIdentifier":"designer","code":"starck","labels":{"fr_FR":"Philippe Starck"}},"propertyPath":"code","invalidValue":{"assetFamilyIdentifier":"designer","code":"starck","labels":{"fr_FR":"Philippe Starck"}},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]');
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeCreationRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_ROUTE,
            [
                'assetFamilyIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'asset_family_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'values' => [],
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_asset_family()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ASSET_ROUTE,
            [
                'assetFamilyIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'asset_family_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'values' => [],
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
            ->forbid();
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader->assetFamily('brand')->load();
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_create', true);

        $activatedLocales = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $logoAttribute = ImageAttribute::create(
            AttributeIdentifier::create('brand', 'logo', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('logo'),
            LabelCollection::fromArray(['fr_FR' => 'Logo', 'en_US' => 'Logo']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png'])
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->create($logoAttribute);
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_create', false);
    }

    public function invalidIdentifiers()
    {
        $longIdentifier = str_repeat('a', 256);

        return [
            'Asset Identifier has a dash character'                                                     => [//rework as it should not be possible
                'invalid-code',
                'brand',
                'brand',
                '[{"messageTemplate":"pim_asset_manager.asset.validation.code.pattern","parameters":{"{{ value }}":"\u0022invalid-code\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"assetFamilyIdentifier":"brand","code":"invalid-code","labels":[]},"propertyPath":"code","invalidValue":"invalid-code","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'            ],
            'Asset Identifier is 256 characters long'                                                   => [
                $longIdentifier,
                'brand',
                'brand',
                sprintf(
                    '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"assetFamilyIdentifier":"brand","code":"%s","labels":[]},"propertyPath":"code","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier
                ),
            ],
        ];
    }
}
