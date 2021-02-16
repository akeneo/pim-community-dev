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
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
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
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class EditActionTest extends ControllerIntegrationTestCase
{
    private const ASSET_EDIT_ROUTE = 'akeneo_asset_manager_asset_edit_rest';

    private WebClientHelper $webClientHelper;
    private InMemoryFileExists $fileExists;
    private InMemoryFindFileDataByFileKey $findFileData;
    private FixturesLoader $fixturesLoader;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->fileExists = $this->get('akeneo_assetmanager.infrastructure.persistence.query.file_exists');
        $this->findFileData = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_file_data_by_file_key');

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_edits_a_asset_details(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Edit/details_ok.json');
    }

    /**
     * @test
     */
    public function it_edits_a_asset_details_by_removing_the_default_image(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Edit/remove_image_ok.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_EDIT_ROUTE,
            [
                'assetCode'                => 'celine_dion',
                'assetFamilyIdentifier' => 'singer',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->assertRequest(
            $this->client,
            'Asset/Edit/unsynchronised_asset_identifier.json'
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_asset_family_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->assertRequest(
            $this->client,
            'Asset/Edit/unsynchronised_asset_family_identifier.json'
        );
    }

    /**
     * @test
     */
    public function it_edits_a_text_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Edit/text_value_ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_an_invalid_text_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Edit/invalid_text_value.json');
    }

    /**
     * @test
     */
    public function it_edits_a_file_value()
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
        $this->webClientHelper->assertRequest($this->client, 'Asset/Edit/image_value_ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_an_invalid_file_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Edit/invalid_image_value.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_a_number_out_of_range()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Edit/invalid_number_out_of_range.json');
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_asset_family()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::ASSET_EDIT_ROUTE,
            [
                'assetCode'                => 'celine_dion',
                'assetFamilyIdentifier' => 'singer',
            ],
            'POST',
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

    private function getAssetRepository(): AssetRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader->assetFamily('designer')->load();
        $this->fixturesLoader->assetFamily('brand')->load();
        $this->fixturesLoader->assetFamily('city')->load();

        $repository = $this->getAssetRepository();
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('philou.png')
            ->setKey('/a/b/c/philou.png');
        $image = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromFileinfo($imageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
        );

        $labelValueEnUS = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Philippe Starck')
        );
        $labelValuefrFR = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Philippe Starck')
        );
        $entityItem = Asset::create(
            AssetIdentifier::fromString('designer_starck_a1677570-a278-444b-ab46-baa1db199392'),
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([$labelValueEnUS, $labelValuefrFR, $image])
        );
        $repository->create($entityItem);

        // text attribute
        $textAttributeIdentifier = AttributeIdentifier::create('designer', 'name', 'fingerprint');
        $textAttribute = TextAttribute::createText(
            $textAttributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->create($textAttribute);

        // textarea attribute
        $textareaAttributeIdentifier = AttributeIdentifier::create('designer', 'description', 'fingerprint');
        $textareaAttribute = TextAttribute::createTextarea(
            $textareaAttributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['fr_FR' => 'Description']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->create($textareaAttribute);

        //website attribute
        $websiteAttributeIdentifier = AttributeIdentifier::create('designer', 'website', 'fingerprint');
        $websiteAttribute = TextAttribute::createText(
            $websiteAttributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('website'),
            LabelCollection::fromArray(['fr_FR' => 'Website']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::fromString(AttributeValidationRule::URL),
            AttributeRegularExpression::createEmpty()
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->create($websiteAttribute);

        // media file attribute
        $portraitAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'portrait', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->create($portraitAttribute);

        $ikeaAsset = Asset::create(
            AssetIdentifier::create('brand', 'ikea', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('ikea'),
            ValueCollection::fromValues([])
        );
        $repository->create($ikeaAsset);

        // number attribute
        $numberAttribute = NumberAttribute::create(
            AttributeIdentifier::create('designer', 'age', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('age'),
            LabelCollection::fromArray(['en_US' => 'Linked brand']),
            AttributeOrder::fromInteger(8),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::fromString('-10'),
            AttributeLimit::fromString('10')
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->create($numberAttribute);


        $parisAsset = Asset::create(
            AssetIdentifier::create('city', 'paris', 'fingerprint'),
            AssetFamilyIdentifier::fromString('city'),
            AssetCode::fromString('paris'),
            ValueCollection::fromValues([])
        );
        $repository->create($parisAsset);
        $lisbonneAsset = Asset::create(
            AssetIdentifier::create('city', 'lisbonne', 'fingerprint'),
            AssetFamilyIdentifier::fromString('city'),
            AssetCode::fromString('lisbonne'),
            ValueCollection::fromValues([])
        );
        $repository->create($lisbonneAsset);
        $moscouAsset = Asset::create(
            AssetIdentifier::create('city', 'moscou', 'fingerprint'),
            AssetFamilyIdentifier::fromString('city'),
            AssetCode::fromString('moscou'),
            ValueCollection::fromValues([])
        );
        $repository->create($moscouAsset);

        $activatedLocales = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }
}
