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

namespace Akeneo\AssetManager\Integration\UI\Web\Attribute;

use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_ATTRIBUTE_ROUTE = 'akeneo_asset_manager_attribute_create_rest';

    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_creates_a_text_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_text_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_a_media_file_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_media_file_ok.json');
    }

    /**
     * TODO: enable back when we will enable it again on reference entity
     */
    public function it_creates_a_asset_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_asset_ok.json');
    }

    /**
     * TODO: enable back when we will enable it again on reference entity
     */
    public function it_creates_a_asset_collection_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_asset_collection_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_an_option_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_option_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_an_option_collection_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_option_collection_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_a_number_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_number_ok.json');
    }


    /**
     * @test
     */
    public function it_returns_an_error_if_the_code_is_invalid()
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/invalid_code.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'assetFamilyIdentifier' => 'celine_dion',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_attribute_create', true);

        $activatedLocales = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brands'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create($assetFamily);
    }
}
