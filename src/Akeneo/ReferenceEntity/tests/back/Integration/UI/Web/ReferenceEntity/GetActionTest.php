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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\ReferenceEntity;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends ControllerIntegrationTestCase
{
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
    public function it_returns_a_reference_entity_details(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'ReferenceEntity/ReferenceEntityDetails/ok.json');
    }

    /**
     * @test
     */
    public function it_returns_a_reference_entity_details_for_which_edition_is_not_allowed(): void
    {
        $this->forbidEdition();
        $this->webClientHelper->assertRequest($this->client, 'ReferenceEntity/ReferenceEntityDetails/ok_not_allowed_to_edit.json');
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_identifier_does_not_exist(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'ReferenceEntity/ReferenceEntityDetails/not_found.json');
    }

    private function loadFixtures(): void
    {
        $queryHandler = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_reference_entity_details');

        $file = new FileInfo();
        $file->setKey('5/6/a/5/56a5955ca1fbdf74d8d18ca6e5f62bc74b867a5d_designer.jpg');
        $file->setOriginalFilename('designer.jpg');

        $entityItem = new ReferenceEntityDetails();
        $entityItem->identifier = (ReferenceEntityIdentifier::fromString('designer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ]);
        $entityItem->image = Image::fromFileInfo($file);
        $entityItem->recordCount = 123;
        $entityItem->attributeAsImage = AttributeAsImageReference::createFromNormalized('designer_portrait_123456');
        $entityItem->attributeAsLabel = AttributeAsLabelReference::createFromNormalized('designer_name_123456');

        $name = new AttributeDetails();
        $name->identifier = 'designer_name_123456';
        $name->referenceEntityIdentifier = 'designer';
        $name->code = 'name';
        $name->isRequired = false;
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
        $bio->referenceEntityIdentifier = 'designer';
        $bio->code = 'bio';
        $bio->isRequired = false;
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
        $portrait->referenceEntityIdentifier = 'designer';
        $portrait->code = 'portrait';
        $portrait->isRequired = false;
        $portrait->order = 2;
        $portrait->valuePerChannel = false;
        $portrait->valuePerLocale = true;
        $portrait->type = 'image';
        $portrait->labels = ['en_US' => 'Portrait', 'fr_FR' => 'Image'];
        $portrait->additionalProperties = [
            'max_file_size'      => '124.12',
            'allowed_extensions' => ['png', 'jpg'],
        ];

        $favoriteColor = new AttributeDetails();
        $favoriteColor->identifier = 'favorite_color_designer_52609e00b7ee307e79eb100099b9a8bf';
        $favoriteColor->referenceEntityIdentifier = 'designer';
        $favoriteColor->code = 'favorite_color';
        $favoriteColor->isRequired = true;
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
        $colors->referenceEntityIdentifier = 'designer';
        $colors->code = 'colors';
        $colors->isRequired = true;
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

        $city = new AttributeDetails();
        $city->identifier = 'city_designer_79eb100099b9a8bf52609e00b7ee307e';
        $city->referenceEntityIdentifier = 'designer';
        $city->code = 'city';
        $city->isRequired = false;
        $city->order = 5;
        $city->valuePerChannel = false;
        $city->valuePerLocale = false;
        $city->type = 'record';
        $city->labels = ['en_US' => 'City'];
        $city->additionalProperties = [
            'record_type' => 'city'
        ];

        $birthdate = new AttributeDetails();
        $birthdate->identifier = 'year_of_birth_designer_79eb100099b9a8bf52609e00b7ee307e';
        $birthdate->referenceEntityIdentifier = 'designer';
        $birthdate->code = 'year_of_birth';
        $birthdate->isRequired = false;
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
            $city,
            $birthdate
        ];
        $queryHandler->save($entityItem);
    }

    private function forbidEdition(): void
    {
        $this->get('akeneo_referenceentity.application.reference_entity_permission.can_edit_reference_entity_query_handler')
            ->forbid();
    }
}
