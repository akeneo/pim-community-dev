<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Attribute;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditActionTest extends ControllerIntegrationTestCase
{
    private const EDIT_ATTRIBUTE_ROUTE = 'akeneo_reference_entities_attribute_edit_rest';
    private const RESPONSES_DIR = 'Attribute/Edit/';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), self::$kernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
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
                'referenceEntityIdentifier' => 'designer',
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
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_reference_entity()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
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
        $this->get('akeneo_referenceentity.application.reference_entity_permission.can_edit_reference_entity_query_handler')
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
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_edit', true);

        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityRepository->create(ReferenceEntity::create(ReferenceEntityIdentifier::fromString('designer'),
            [],
            Image::createEmpty()
        ));
        $referenceEntityRepository->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [],
            Image::createEmpty()
        ));

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', md5('fingerprint')),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(100),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $portrait = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'portrait', md5('fingerprint')),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png'])
        );

        $favoriteColor = OptionAttribute::create(
            AttributeIdentifier::create('designer', 'favorite_color', md5('fingerprint')),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray(['fr_FR' => 'Couleur favorite', 'en_US' => 'Favorite color']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $age = NumberAttribute::create(
            AttributeIdentifier::create('designer', 'age', md5('fingerprint')),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('age'),
            LabelCollection::fromArray(['fr_FR' => 'Age', 'en_US' => 'Age']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
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

        $activatedLocales = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    private function revokeEditRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_edit', false);
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
            // Todo: Override parameter 'reference_entity_option_limit_per_list_attribute' in kernel
            // 'Limit of options per attribute is reached' => ['Option' . DIRECTORY_SEPARATOR . 'limit_of_options_reached.json'],
        ];
    }

    public function getUnsynchronisedIdentifiersRequests()
    {
        return [
            'Unsynchronised attribute identifier'        => ['Attribute/Edit/unsynchronised_attribute_identifier.json'],
            'Unsynchronised reference entity identifier' => ['Attribute/Edit/unsynchronised_reference_entity_identifier.json'],
        ];
    }
}
