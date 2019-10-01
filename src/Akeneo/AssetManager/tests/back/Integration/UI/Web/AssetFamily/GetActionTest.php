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

use Akeneo\AssetManager\Common\Helper\AuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
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
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
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
        $file->setKey('5/6/a/5/56a5955ca1fbdf74d8d18ca6e5f62bc74b867a5d_packshot.jpg');
        $file->setOriginalFilename('packshot.jpg');

        $entityItem = new AssetFamilyDetails();
        $entityItem->identifier = (AssetFamilyIdentifier::fromString('packshot'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Packshot',
            'fr_FR' => 'Packshot',
        ]);
        $entityItem->image = Image::fromFileInfo($file);
        $entityItem->assetCount = 123;
        $entityItem->attributeAsImage = AttributeAsImageReference::createFromNormalized('packshot_image_123456');
        $entityItem->attributeAsLabel = AttributeAsLabelReference::createFromNormalized('packshot_name_123456');

        $name = new AttributeDetails();
        $name->identifier = 'packshot_name_123456';
        $name->assetFamilyIdentifier = 'packshot';
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

        $image = new AttributeDetails();
        $image->identifier = 'packshot_image_123456';
        $image->assetFamilyIdentifier = 'packshot';
        $image->code = 'image';
        $image->isRequired = false;
        $image->order = 2;
        $image->valuePerChannel = false;
        $image->valuePerLocale = true;
        $image->type = 'image';
        $image->labels = ['en_US' => 'Image', 'fr_FR' => 'Image'];
        $image->additionalProperties = [
            'max_file_size'      => '124.12',
            'allowed_extensions' => ['png', 'jpg'],
        ];

        $favoriteColor = new AttributeDetails();
        $favoriteColor->identifier = 'favorite_color_packshot_52609e00b7ee307e79eb100099b9a8bf';
        $favoriteColor->assetFamilyIdentifier = 'packshot';
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

        $entityItem->attributes = [
            $name,
            $image,
            $favoriteColor
        ];
        $queryHandler->save($entityItem);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }

    private function forbidEdition(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
            ->forbid();
    }
}
