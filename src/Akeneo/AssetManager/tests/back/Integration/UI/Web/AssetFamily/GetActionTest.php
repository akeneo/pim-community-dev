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

use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class GetActionTest extends ControllerIntegrationTestCase
{
    /** @var WebClientHelper */
    private $webClientHelper;

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
    public function it_returns_an_asset_family_details(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'AssetFamily/AssetFamilyDetails/ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_asset_family_details_for_which_edition_is_not_allowed(): void
    {
        $this->forbidEdition();
        $this->webClientHelper->assertRequest($this->client, 'AssetFamily/AssetFamilyDetails/ok_not_allowed_to_edit.json');
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_identifier_does_not_exist(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'AssetFamily/AssetFamilyDetails/not_found.json');
    }

    private function loadFixtures(): void
    {
        $queryHandler = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_family_details');

        $file = new FileInfo();
        $file->setKey('5/6/a/5/56a5955ca1fbdf74d8d18ca6e5f62bc74b867a5d_designer.jpg');
        $file->setOriginalFilename('designer.jpg');

        $entityItem = new AssetFamilyDetails();
        $entityItem->identifier = (AssetFamilyIdentifier::fromString('designer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ]);
        $entityItem->image = Image::fromFileInfo($file);
        $entityItem->assetCount = 123;
        $entityItem->attributeAsMainMedia = AttributeAsMainMediaReference::createFromNormalized('designer_portrait_123456');
        $entityItem->attributeAsLabel = AttributeAsLabelReference::createFromNormalized('designer_name_123456');
        $entityItem->transformations = new ConnectorTransformationCollection([]);
        $entityItem->namingConvention = NamingConvention::createFromNormalized([
            'source' => ['property' => 'media', 'locale' => null, 'channel' => null],
            'pattern' => '/the_pattern/',
            'abort_asset_creation_on_error' => true,
        ]);
        $entityItem->productLinkRules = [
            [
                'product_selections' => [
                    [
                        'field' => 'fulltext',
                        'operator' => 'IN',
                        'value' => 'value',
                        'channel' => null,
                        'locale' => null
                    ]
                ],
                'assign_assets_to' => [
                    [
                        'attribute' => 'main',
                        'channel' => null,
                        'locale' => null,
                        'mode' => 'add'
                    ]
                ]
            ]
        ];

        $name = new AttributeDetails();
        $name->identifier = 'designer_name_123456';
        $name->assetFamilyIdentifier = 'designer';
        $name->code = 'name';
        $name->isRequired = false;
        $name->isReadOnly = true;
        $name->order = 0;
        $name->valuePerChannel = false;
        $name->valuePerLocale = true;
        $name->type = 'text';
        $name->labels = ['en_US' => 'Name', 'fr_FR' => 'Nom'];
        $name->additionalProperties = [
            'max_length'          => 255,
            'is_textarea'         => false,
            'is_rich_text_editor' => false,
            'validation_rule'     => "none",
            'regular_expression'  => null,
        ];

        $bio = new AttributeDetails();
        $bio->identifier = 'designer_bio_123456';
        $bio->assetFamilyIdentifier = 'designer';
        $bio->code = 'bio';
        $bio->isRequired = false;
        $bio->isReadOnly = false;
        $bio->order = 1;
        $bio->valuePerChannel = false;
        $bio->valuePerLocale = true;
        $bio->type = 'text';
        $bio->labels = ['en_US' => 'Bio', 'fr_FR' => 'Biographie'];
        $bio->additionalProperties = [
            'max_length'          => 255,
            'is_textarea'         => false,
            'is_rich_text_editor' => false,
            'validation_rule'     => "none",
            'regular_expression'  => null,
        ];

        $portrait = new AttributeDetails();
        $portrait->identifier = 'designer_portrait_123456';
        $portrait->assetFamilyIdentifier = 'designer';
        $portrait->code = 'portrait';
        $portrait->isRequired = false;
        $portrait->isReadOnly = false;
        $portrait->order = 2;
        $portrait->valuePerChannel = false;
        $portrait->valuePerLocale = true;
        $portrait->type = 'media_file';
        $portrait->labels = ['en_US' => 'Portrait', 'fr_FR' => 'Image'];
        $portrait->additionalProperties = [
            'max_file_size'      => '124.12',
            'allowed_extensions' => ['png', 'jpg'],
            'media_type' => MediaType::IMAGE
        ];

        $favoriteColor = new AttributeDetails();
        $favoriteColor->identifier = 'favorite_color_designer_52609e00b7ee307e79eb100099b9a8bf';
        $favoriteColor->assetFamilyIdentifier = 'designer';
        $favoriteColor->code = 'favorite_color';
        $favoriteColor->isRequired = true;
        $favoriteColor->isReadOnly = false;
        $favoriteColor->order = 3;
        $favoriteColor->valuePerChannel = true;
        $favoriteColor->valuePerLocale = true;
        $favoriteColor->type = 'option';
        $favoriteColor->labels = ['en_US' => 'Favorite color'];
        $favoriteColor->additionalProperties = [
            'options' => [
                [
                    'code'   => 'red',
                    'labels' => ['en_US' => 'Red', 'fr_FR' => 'Rouge'],
                ],
                [
                    'code'   => 'green',
                    'labels' => ['en_US' => 'Green', 'fr_FR' => 'Vert'],
                ],
            ],
        ];

        $colors = new AttributeDetails();
        $colors->identifier = 'colors_designer_52609e00b7ee307e79eb100099b9a8bf';
        $colors->assetFamilyIdentifier = 'designer';
        $colors->code = 'colors';
        $colors->isRequired = true;
        $colors->isReadOnly = false;
        $colors->order = 4;
        $colors->valuePerChannel = true;
        $colors->valuePerLocale = true;
        $colors->type = 'option_collection';
        $colors->labels = ['en_US' => 'Colors'];
        $colors->additionalProperties = [
            'options' => [
                [
                    'code'   => 'red',
                    'labels' => ['en_US' => 'Red', 'fr_FR' => 'Rouge'],
                ],
                [
                    'code'   => 'blue',
                    'labels' => ['en_US' => 'Blue', 'fr_FR' => 'Bleu'],
                ],
            ],
        ];

        $birthdate = new AttributeDetails();
        $birthdate->identifier = 'year_of_birth_designer_79eb100099b9a8bf52609e00b7ee307e';
        $birthdate->assetFamilyIdentifier = 'designer';
        $birthdate->code = 'year_of_birth';
        $birthdate->isRequired = false;
        $birthdate->isReadOnly = false;
        $birthdate->order = 6;
        $birthdate->valuePerChannel = false;
        $birthdate->valuePerLocale = false;
        $birthdate->type = 'number';
        $birthdate->labels = ['en_US' => 'Year of Birth'];
        $birthdate->additionalProperties = [
            'decimals_allowed' => false,
            'min_value'  => '10',
            'max_value'  => '50'
        ];

        $entityItem->attributes = [
            $name,
            $bio,
            $portrait,
            $favoriteColor,
            $colors,
            $birthdate
        ];
        $queryHandler->save($entityItem);
    }

    private function forbidEdition(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
             ->forbid();
    }
}
