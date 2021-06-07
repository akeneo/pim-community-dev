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

use Akeneo\AssetManager\Common\Fake\SecurityFacadeStub;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\Channel\Component\Model\Locale;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class EditActionTest extends ControllerIntegrationTestCase
{
    private const ASSET_FAMILY_EDIT_ROUTE = 'akeneo_asset_manager_asset_family_edit_rest';

    private WebClientHelper $webClientHelper;

    private AttributeRepositoryInterface $attributeRepository;

    private SecurityFacadeStub $securityFacade;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->securityFacade = $this->get('oro_security.security_facade');
    }

    /**
     * @test
     */
    public function it_edits_an_asset_family_details(): void
    {
        $this->allowEditRights();
        $attributeIdentifier = $this->getIdentifierForAttribute(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
        );

        $postContent = [
            'identifier' => 'designer',
            'labels' => [
                'en_US' => 'foo',
                'fr_FR' => 'bar',
            ],
            'attributeAsMainMedia' => $attributeIdentifier->stringValue(),
            'image' => [
                'filePath' => '/path/image.jpg',
                'originalFilename' => 'image.jpg'
            ],
            'productLinkRules' => '[]',
            'transformations' => [],
            'namingConvention' => '{"source": {"property": "code", "locale": null, "channel": null}, "pattern": "/pattern/", "abort_asset_creation_on_error": true}',
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);

        $repository = $this->getEnrichEntityRepository();
        $entityItem = $repository->getByIdentifier(AssetFamilyIdentifier::fromString($postContent['identifier']));

        Assert::assertEquals(array_keys($postContent['labels']), $entityItem->getLabelCodes());
        Assert::assertEquals($postContent['labels']['en_US'], $entityItem->getLabel('en_US'));
        Assert::assertEquals($postContent['labels']['fr_FR'], $entityItem->getLabel('fr_FR'));
        Assert::assertInstanceOf(NamingConvention::class, $entityItem->getNamingConvention());
        Assert::assertInstanceOf(RuleTemplateCollection::class, $entityItem->getRuleTemplateCollection());
    }

    /** @test */
    public function it_returns_errors_when_the_json_strings_are_not_valid(): void
    {
        $this->allowEditRights();
        $attributeIdentifier = $this->getIdentifierForAttribute(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
        );

        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
                'fr_FR' => 'bar',
            ],
            'attributeAsMainMedia' => $attributeIdentifier->stringValue(),
            'image'      => [
                'filePath'         => '/path/image.jpg',
                'originalFilename' => 'image.jpg'
            ],
            'productLinkRules' => null,
            'transformations' => [],
            'namingConvention' => '[invalid_naming_convention',
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
        $errors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('messageTemplate', $errors[0]);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertArrayHasKey('propertyPath', $errors[0]);
        $this->assertArrayHasKey('parameters', $errors[0]);
        $this->assertSame('This value should be valid JSON.', $errors[0]['message']);
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'brand'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => 'wrong_identifier',
                'labels'     => [
                    'en_US' => 'foo',
                    'fr_FR' => 'bar',
                ],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST, '"Asset family identifier provided in the route and the one given in the body of your request are different"');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'any_id'],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_access_denied_if_the_user_does_not_have_permissions(): void
    {
        $this->allowEditRights();
        $this->client->followRedirects(false);
        $this->forbidsEdit();
        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
            ],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_an_access_denied_when_the_user_does_not_have_the_acl_permission()
    {
        $this->client->followRedirects(false);
        $this->revokeEditRights();
        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
            ],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_does_not_take_in_account_transformation_if_manage_transformation_is_not_granted()
    {
        $this->allowEditRights();
        $this->revokeManageTransformationRights();
        $attributeIdentifier = $this->getIdentifierForAttribute(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
        );

        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
                'fr_FR' => 'bar',
            ],
            'attributeAsMainMedia' => $attributeIdentifier->stringValue(),
            'image'      => [
                'filePath'         => '/path/image.jpg',
                'originalFilename' => 'image.jpg'
            ],
            'productLinkRules' => 'null',
            'transformations' => '[{"foo": "bar"}]',
            'namingConvention' => '{}',
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);

        $repository = $this->getEnrichEntityRepository();
        $entityItem = $repository->getByIdentifier(AssetFamilyIdentifier::fromString($postContent['identifier']));

        Assert::assertEquals(array_keys($postContent['labels']), $entityItem->getLabelCodes());
        Assert::assertEquals($postContent['labels']['en_US'], $entityItem->getLabel('en_US'));
        Assert::assertEquals($postContent['labels']['fr_FR'], $entityItem->getLabel('fr_FR'));
        Assert::assertEquals(TransformationCollection::noTransformation(), $entityItem->getTransformationCollection());
    }

    /** @test */
    public function it_does_not_take_in_account_naming_convention_if_manage_is_not_granted()
    {
        $this->allowEditRights();
        $this->revokeManageProductLinkRuleRights();
        $attributeIdentifier = $this->getIdentifierForAttribute(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
        );

        $postContent = [
            'identifier' => 'designer',
            'labels' => ['en_US' => 'foo', 'fr_FR' => 'bar'],
            'attributeAsMainMedia' => $attributeIdentifier->stringValue(),
            'image' => [
                'filePath' => '/path/image.jpg',
                'originalFilename' => 'image.jpg'
            ],
            'productLinkRules' => 'null',
            'transformations' => [],
            'namingConvention' => '{"source": {"property": "code", "locale": null, "channel": null}, "pattern": "/pattern/", "abort_asset_creation_on_error": true}',
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json',],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
        $repository = $this->getEnrichEntityRepository();
        $entityItem = $repository->getByIdentifier(AssetFamilyIdentifier::fromString($postContent['identifier']));

        Assert::assertEquals(new NullNamingConvention(), $entityItem->getNamingConvention());
    }

    private function getEnrichEntityRepository(): AssetFamilyRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
            ->forbid();
    }

    private function revokeEditRights(): void
    {
        $this->securityFacade->setIsGranted('akeneo_assetmanager_asset_family_edit', false);
    }

    private function allowEditRights(): void
    {
        $this->securityFacade->setIsGranted('akeneo_assetmanager_asset_family_edit', true);
        $this->securityFacade->setIsGranted('akeneo_assetmanager_asset_family_manage_transformation', true);
        $this->securityFacade->setIsGranted('akeneo_assetmanager_asset_family_manage_product_link_rule', true);
    }

    private function revokeManageTransformationRights(): void
    {
        $this->securityFacade->setIsGranted('akeneo_assetmanager_asset_family_manage_transformation', false);
    }

    private function revokeManageProductLinkRuleRights(): void
    {
        $this->securityFacade->setIsGranted('akeneo_assetmanager_asset_family_manage_product_link_rule', false);
    }

    private function loadFixtures(): void
    {
        $assetFamilyRepository = $this->getEnrichEntityRepository();

        $entityItem = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($entityItem);

        $fr = new Locale();
        $fr->setId(1);
        $fr->setCode('fr_FR');
        $this->get('pim_catalog.repository.locale')->save($fr);

        $activatedLocales = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    private function getIdentifierForAttribute(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);

        foreach ($attributes as $attribute) {
            if ($attribute->getCode()->equals($attributeCode)) {
                return $attribute->getIdentifier();
            }
        }

        throw new \Exception(
            sprintf(
                'Cannot find any attribute for asset family "%s" and code "%s"',
                $assetFamilyIdentifier->normalize(),
                (string)$attributeCode
            )
        );
    }
}
