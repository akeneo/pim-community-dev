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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAttributesDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindAttributesDetailsInterface */
    private $findAttributesDetails;

    /** @var array */
    private $fixturesDesigner;

    /** @var array */
    private $fixturesBrand;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributesDetails = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_attributes_details');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_details_for_an_asset_family()
    {
        $attributeDetails = $this->findAttributesDetails->find(AssetFamilyIdentifier::fromString('designer'));

        $this->assertCount(7, $attributeDetails);
        $this->assertNameAttribute($attributeDetails);
        $this->assertEmailAttribute($attributeDetails);
        $this->assertCustomRegex($attributeDetails);
        $this->assertLongDescriptionAttribute($attributeDetails);
        $this->assertMediaFileAttribute($attributeDetails);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixturesDesigner = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['name', 'email', 'regex', 'long_description', 'main_image'])
            ->load();

        $this->fixturesBrand = $this->fixturesLoader
            ->assetFamily('brand')
            ->load();
    }

    /**
     * @param $attributeDetails
     *
     */
    private function assertNameAttribute($attributeDetails): void
    {
        $actualName = $this->getAttributeWithCode($attributeDetails, 'name');

        $expectedName = new AttributeDetails();
        $expectedName->type = 'text';
        $expectedName->identifier = (string) $this->fixturesDesigner['attributes']['name']->getIdentifier();
        $expectedName->assetFamilyIdentifier = 'designer';
        $expectedName->code = 'name';
        $expectedName->labels = ['en_US' => 'Name', 'fr_FR' => 'Nom'];
        $expectedName->order = 2;
        $expectedName->isRequired = false;
        $expectedName->valuePerChannel = false;
        $expectedName->valuePerLocale = true;
        $expectedName->isReadOnly = false;
        $expectedName->additionalProperties = [
            'max_length' => 25,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => 'none',
            'regular_expression' => null,
        ];

        $this->assertEquals($expectedName, $actualName);
    }

    private function assertEmailAttribute($attributeDetails): void
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'email');

        $expectedEmail = new AttributeDetails();
        $expectedEmail->type = 'text';
        $expectedEmail->identifier = (string) $this->fixturesDesigner['attributes']['email']->getIdentifier();
        $expectedEmail->assetFamilyIdentifier = 'designer';
        $expectedEmail->code = 'email';
        $expectedEmail->labels = ['en_US' => 'Email', 'fr_FR' => 'Email'];
        $expectedEmail->order = 3;
        $expectedEmail->isRequired = true;
        $expectedEmail->valuePerChannel = false;
        $expectedEmail->valuePerLocale = false;
        $expectedEmail->isReadOnly = false;
        $expectedEmail->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => 'email',
            'regular_expression' => null,
        ];

        $this->assertEquals($expectedEmail, $actualEmail);
    }

    private function assertCustomRegex($attributeDetails)
    {
        $actualRegex = $this->getAttributeWithCode($attributeDetails, 'regex');

        $expectedRegex = new AttributeDetails();
        $expectedRegex->type = 'text';
        $expectedRegex->identifier = (string) $this->fixturesDesigner['attributes']['regex']->getIdentifier();
        $expectedRegex->assetFamilyIdentifier = 'designer';
        $expectedRegex->code = 'regex';
        $expectedRegex->labels = ['en_US' => 'Regex'];
        $expectedRegex->order = 4;
        $expectedRegex->isRequired = true;
        $expectedRegex->valuePerChannel = true;
        $expectedRegex->valuePerLocale = true;
        $expectedRegex->isReadOnly = false;
        $expectedRegex->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => 'regular_expression',
            'regular_expression' => '/\w+/',
        ];

        $this->assertEquals($expectedRegex, $actualRegex);
    }

    private function assertLongDescriptionAttribute($attributeDetails)
    {
        $actualLongDescription = $this->getAttributeWithCode($attributeDetails, 'long_description');

        $expectedLongDescription = new AttributeDetails();
        $expectedLongDescription->type = 'text';
        $expectedLongDescription->identifier = (string) $this->fixturesDesigner['attributes']['long_description']->getIdentifier();
        $expectedLongDescription->assetFamilyIdentifier = 'designer';
        $expectedLongDescription->code = 'long_description';
        $expectedLongDescription->labels = ['en_US' => 'Long description'];
        $expectedLongDescription->order = 5;
        $expectedLongDescription->isRequired = true;
        $expectedLongDescription->valuePerChannel = true;
        $expectedLongDescription->valuePerLocale = true;
        $expectedLongDescription->isReadOnly = false;
        $expectedLongDescription->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => true,
            'is_rich_text_editor' => true,
            'validation_rule' => 'none',
            'regular_expression' => null,
        ];

        $this->assertEquals($expectedLongDescription, $actualLongDescription);
    }

    /**
     * @param $attributeDetails
     */
    private function assertMediaFileAttribute($attributeDetails): void
    {
        $actualImage = $this->getAttributeWithCode($attributeDetails, 'main_image');

        $expectedImage = new AttributeDetails();
        $expectedImage->type = 'media_file';
        $expectedImage->identifier = (string) $this->fixturesDesigner['attributes']['main_image']->getIdentifier();
        $expectedImage->assetFamilyIdentifier = 'designer';
        $expectedImage->code = 'main_image';
        $expectedImage->labels = ['en_US' => 'Portrait'];
        $expectedImage->order = 6;
        $expectedImage->isRequired = true;
        $expectedImage->isReadOnly = false;
        $expectedImage->valuePerChannel = true;
        $expectedImage->valuePerLocale = false;
        $expectedImage->isReadOnly = false;
        $expectedImage->additionalProperties = [
            'max_file_size' => '1000',
            'allowed_extensions' => ['png'],
            'media_type' => MediaType::IMAGE
        ];

        $this->assertEquals($expectedImage, $actualImage);
    }

    // TODO: add test case for new attribute types

    private function getAttributeWithCode(array $attributesDetails, string $attributeCode): AttributeDetails
    {
        foreach ($attributesDetails as $attributeDetails) {
            if ($attributeCode === (string) $attributeDetails->code) {
                return $attributeDetails;
            }
        }

        throw new \LogicException(sprintf('Attribute details with attribute code "%s" not found.', $attributeCode));
    }
}
