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

use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
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
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;

class GetActionTest extends ControllerIntegrationTestCase
{
    private WebClientHelper $webClientHelper;

    private AttributeRepositoryInterface $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_a_assets_detail()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/AssetDetails/ok.json');
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_asset_identifier_does_not_exist()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/AssetDetails/not_found.json');
    }

    private function loadFixtures(): void
    {
        $textAttributeIdentifier = AttributeIdentifier::create('designer', 'name', 'fingerprint');
        $textAttribute = TextAttribute::createText(
            $textAttributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($textAttribute);

        $textareaAttributeIdentifier = AttributeIdentifier::create('designer', 'description', 'fingerprint');
        $textareaAttribute = TextAttribute::createTextarea(
            $textareaAttributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['fr_FR' => 'Description']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $this->attributeRepository->create($textareaAttribute);

        $websiteAttributeIdentifier = AttributeIdentifier::create('designer', 'website', 'fingerprint');
        $websiteAttribute = TextAttribute::createText(
            $websiteAttributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('website'),
            LabelCollection::fromArray(['fr_FR' => 'Website']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::fromString(AttributeValidationRule::URL),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($websiteAttribute);

        // media file attribute
        $portraitAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'portrait', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );
        $this->attributeRepository->create($portraitAttribute);

        // media file attribute
        $age = NumberAttribute::create(
            AttributeIdentifier::create('designer', 'age', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('age'),
            LabelCollection::fromArray(['fr_FR' => 'Age', 'en_US' => 'Age']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::fromString('10'),
            AttributeLimit::fromString('20')
        );
        $this->attributeRepository->create($age);

        $values = [
            [
                'attribute' => $textAttribute->normalize(),
                'channel' => null,
                'locale' => null,
                'data' => 'Philippe Starck'
            ],
            [
                'attribute' => $textareaAttribute->normalize(),
                'channel' => null,
                'locale' => 'en_US',
                'data' => null,
            ],
            [
                'attribute' => $textareaAttribute->normalize(),
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => null,
            ],
            [
                'attribute' => $websiteAttribute->normalize(),
                'channel' => null,
                'locale' => null,
                'data' => null,
            ],
            [
                'attribute' => $portraitAttribute->normalize(),
                'channel' => null,
                'locale' => null,
                'data' => null,
            ],
            [
                'attribute' => $age->normalize(),
                'channel'   => null,
                'locale'    => null,
                'data'      => null,
            ]
        ];

        $starck = new AssetDetails(
            AssetIdentifier::fromString('designer_starck_a1677570-a278-444b-ab46-baa1db199392'),
            AssetFamilyIdentifier::fromString('designer'),
            $portraitAttribute->getIdentifier(),
            AssetCode::fromString('starck'),
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']),
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2020-05-14T09:24:03-07:00'),
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2020-05-14T09:30:03-07:00'),
            [],
            $values,
            true
        );

        $findAssetDetails = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_details');
        $findAssetDetails->save($starck);
    }
}
