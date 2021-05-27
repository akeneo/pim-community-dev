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

use Akeneo\AssetManager\Common\Helper\AuthenticatedClient;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class DeleteActionTest extends ControllerIntegrationTestCase
{
    private const DELETE_ATTRIBUTE_ROUTE = 'akeneo_asset_manager_attribute_delete_rest';

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
    public function it_deletes_an_attribute_and_its_assets_values(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint')),
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeDeletionRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint')),
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /** @test */
    public function it_returns_an_error_when_the_attribute_does_not_exist()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => 'unknown',
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => 'name',
                'assetFamilyIdentifier' => 'celine_dion',
            ],
            'DELETE'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_asset_family()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint')),
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
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
        $attributeRepository = $this->getAttributeRepository();
        $assetFamilyRepository = $this->getAssetFamilyRepository();

        $assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString('designer'),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );

        $attributeItem = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', md5('fingerprint')),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributeRepository->create($attributeItem);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_attribute_delete', true);
    }

    private function getAttributeRepository(): AttributeRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
    }

    private function getAssetFamilyRepository(): AssetFamilyRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_attribute_delete', false);
    }
}
