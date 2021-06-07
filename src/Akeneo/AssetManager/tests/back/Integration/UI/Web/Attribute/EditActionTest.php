<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\UI\Web\Attribute;

use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditActionTest extends ControllerIntegrationTestCase
{
    private const EDIT_ATTRIBUTE_ROUTE = 'akeneo_asset_manager_attribute_edit_rest';
    private const RESPONSES_DIR = 'Attribute/Edit/';

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
    public function it_edits_a_text_attribute_properties()
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Edit/ok.json');
    }

    /**
     * @test
     */
    public function it_does_not_edit_if_the_attribute_does_not_exist()
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Edit/attribute_does_not_exist.json');
    }

    /**
     * @test
     * @dataProvider getValidationErrorsRequests
     */
    public function it_normalizes_validation_errors(string $requestFile)
    {
        $this->webClientHelper->assertRequest($this->client, $requestFile);
    }

    /**
     * @test
     * @dataProvider getUnsynchronisedIdentifiersRequests
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_one_in_the_body(
        string $requestFile
    ) {
        $this->webClientHelper->assertRequest($this->client, $requestFile);
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $attributeIdentifier = sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint'));

        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
                'attributeIdentifier'       => $attributeIdentifier,
            ],
            'POST'
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
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
                'attributeIdentifier'       => sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint')),
            ],
            'POST',
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

    /** @test */
    public function it_returns_an_error_when_the_user_does_not_have_the_rights()
    {
        $this->revokeEditRights();
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Edit/forbidden.json');
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_attribute_edit', true);

        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create(AssetFamily::create(AssetFamilyIdentifier::fromString('designer'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        ));
        $assetFamilyRepository->create(AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        ));

        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', md5('fingerprint')),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(100),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $portrait = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'portrait', md5('fingerprint')),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $favoriteColor = OptionAttribute::create(
            AttributeIdentifier::create('designer', 'favorite_color', md5('fingerprint')),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray(['fr_FR' => 'Couleur favorite', 'en_US' => 'Favorite color']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $age = NumberAttribute::create(
            AttributeIdentifier::create('designer', 'age', md5('fingerprint')),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('age'),
            LabelCollection::fromArray(['fr_FR' => 'Age', 'en_US' => 'Age']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::fromString('0'),
            AttributeLimit::fromString('999')
        );
        $attributeRepository->create($name);
        $attributeRepository->create($portrait);
        $attributeRepository->create($favoriteColor);
        $attributeRepository->create($age);

        $activatedLocales = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    private function revokeEditRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_attribute_edit', false);
    }

    public function getValidationErrorsRequests(): array
    {
        return [
//            'Invalid allowed extension'                 => ['Attribute/Edit/allowed_extensions_is_invalid.json'],
//            'Max file size is invalid'                  => ['Attribute/Edit/max_file_size_is_invalid.json'],
//            'Invalid option codes regular expression'   => ['Attribute/Edit/Option/invalid_option_code_regular_expression.json'],
//            'Option code is blank'                      => ['Attribute/Edit/Option/invalid_option_code_blank.json'],
//            'Some options are duplicated'               => ['Attribute/Edit/Option/invalid_options_duplicated.json'],
            'Number: min and max incoherent'            => ['Attribute/Edit/Number/min_max_incoherent.json']
            // Todo: Override parameter 'asset_family_option_limit_per_list_attribute' in kernel
            // 'Limit of options per attribute is reached' => ['Option' . DIRECTORY_SEPARATOR . 'limit_of_options_reached.json'],
        ];
    }

    public function getUnsynchronisedIdentifiersRequests()
    {
        return [
            'Unsynchronised attribute identifier'        => ['Attribute/Edit/unsynchronised_attribute_identifier.json'],
            'Unsynchronised asset family identifier' => ['Attribute/Edit/unsynchronised_asset_family_identifier.json'],
        ];
    }
}
