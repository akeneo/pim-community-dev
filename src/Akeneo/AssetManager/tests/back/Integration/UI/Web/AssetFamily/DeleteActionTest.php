<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\UI\Web\AssetFamily;

use Akeneo\AssetManager\Common\Helper\AuthenticatedClient;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\Channel\Component\Model\Locale;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class DeleteActionTest extends ControllerIntegrationTestCase
{
    private const ASSET_FAMILY_DELETE_ROUTE = 'akeneo_asset_manager_asset_family_delete_rest';

    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->resetDB();
        $this->loadFixtures();
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_deletes_an_asset_family_given_an_identifier()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), 204, '');
    }

    /**
     * @test
     */
    public function it_redirects_if_the_request_is_not_an_xml_http_request()
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE'
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_asset_family_identifier_is_not_valid()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_DELETE_ROUTE,
            ['identifier' => 'des igner'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert500ServerError($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_user_does_not_have_the_acl_to_do_this_action()
    {
        $this->revokeDeletionRights();

        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_there_is_no_asset_family_with_the_given_identifier()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_DELETE_ROUTE,
            ['identifier' => 'unknown'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_asset_family_has_some_assets()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_DELETE_ROUTE,
            ['identifier' => 'brand'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $expectedResponse = '[{"messageTemplate":"pim_asset_manager.asset_family.validation.assets.should_have_no_asset","parameters":{"%asset_family_identifier%":[]},"plural":null,"message":"You cannot delete this family because assets exist for this family","root":{"identifier":"brand"},"propertyPath":"","invalidValue":{"identifier":"brand"},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]';

        $this->webClientHelper->assertResponse($this->client->getResponse(), 400, $expectedResponse);
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_asset_family()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_FAMILY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
            ->forbid();
    }

    private function getEnrichEntityRepository(): AssetFamilyRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
    }

    private function getAssetRepository(): AssetRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $assetFamilyRepository = $this->getEnrichEntityRepository();
        $assetRepository = $this->getAssetRepository();

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

        $entityItem = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'en_US' => 'Brand',
                'fr_FR' => 'Marque',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($entityItem);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $assetCode = AssetCode::fromString('asus');
        $labelValueEnUS = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('ASUS')
        );
        $labelValuefrFR = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('ASUS')
        );

        $assetItem = Asset::create(
            $assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode),
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([$labelValueEnUS, $labelValuefrFR])
        );
        $assetRepository->create($assetItem);

        $fr = new Locale();
        $fr->setId(1);
        $fr->setCode('fr_FR');
        $this->get('pim_catalog.repository.locale')->save($fr);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_family_delete', true);
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_family_delete', false);
    }
}
