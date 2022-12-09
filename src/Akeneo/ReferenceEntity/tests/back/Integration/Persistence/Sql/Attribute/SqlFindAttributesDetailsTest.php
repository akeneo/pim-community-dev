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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindAttributesDetailsTest extends SqlIntegrationTestCase
{
    private FindAttributesDetailsInterface $findAttributesDetails;
    private array $fixturesDesigner;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributesDetails = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_attributes_details');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_details_for_a_reference_entity(): void
    {
        $attributeDetails = $this->findAttributesDetails->find(ReferenceEntityIdentifier::fromString('designer'));

        $this->assertCount(8, $attributeDetails);
        $this->assertNameAttribute($attributeDetails);
        $this->assertEmailAttribute($attributeDetails);
        $this->assertCustomRegex($attributeDetails);
        $this->assertLongDescriptionAttribute($attributeDetails);
        $this->assertImageAttribute($attributeDetails);
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_details_for_a_given_identifier(): void
    {
        $attributeDetails = $this->findAttributesDetails->findByIdentifier(
            $this->fixturesDesigner['attributes']['main_material']->getIdentifier(),
        );

        $this->assertMainMaterialAttribute($attributeDetails);
    }

    /**
     * @test
     */
    public function it_returns_null_when_attribute_is_not_found(): void
    {
        $attributeDetails = $this->findAttributesDetails->findByIdentifier(
            AttributeIdentifier::fromString('not_found'),
        );

        $this->assertNull($attributeDetails);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixturesDesigner = $this->fixturesLoader
            ->referenceEntity('designer')
            ->withAttributes(['name', 'email', 'regex', 'long_description', 'main_image', 'main_material'])
            ->load();
    }

    private function assertNameAttribute(array $attributeDetails): void
    {
        $actualName = $this->getAttributeWithCode($attributeDetails, 'name');

        $expectedName = new AttributeDetails();
        $expectedName->type = 'text';
        $expectedName->identifier = (string) $this->fixturesDesigner['attributes']['name']->getIdentifier();
        $expectedName->referenceEntityIdentifier = 'designer';
        $expectedName->code = 'name';
        $expectedName->labels = ['en_US' => 'Name', 'fr_FR' => 'Nom'];
        $expectedName->order = 2;
        $expectedName->isRequired = false;
        $expectedName->valuePerChannel = false;
        $expectedName->valuePerLocale = true;
        $expectedName->additionalProperties = [
            'max_length' => 25,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => 'none',
            'regular_expression' => null,
        ];

        $this->assertEquals($expectedName, $actualName);
    }

    private function assertEmailAttribute(array $attributeDetails): void
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'email');

        $expectedEmail = new AttributeDetails();
        $expectedEmail->type = 'text';
        $expectedEmail->identifier = (string) $this->fixturesDesigner['attributes']['email']->getIdentifier();
        $expectedEmail->referenceEntityIdentifier = 'designer';
        $expectedEmail->code = 'email';
        $expectedEmail->labels = ['en_US' => 'Email', 'fr_FR' => 'Email'];
        $expectedEmail->order = 3;
        $expectedEmail->isRequired = true;
        $expectedEmail->valuePerChannel = false;
        $expectedEmail->valuePerLocale = false;
        $expectedEmail->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => 'email',
            'regular_expression' => null,
        ];

        $this->assertEquals($expectedEmail, $actualEmail);
    }

    private function assertCustomRegex(array $attributeDetails): void
    {
        $actualRegex = $this->getAttributeWithCode($attributeDetails, 'regex');

        $expectedRegex = new AttributeDetails();
        $expectedRegex->type = 'text';
        $expectedRegex->identifier = (string) $this->fixturesDesigner['attributes']['regex']->getIdentifier();
        $expectedRegex->referenceEntityIdentifier = 'designer';
        $expectedRegex->code = 'regex';
        $expectedRegex->labels = ['en_US' => 'Regex'];
        $expectedRegex->order = 4;
        $expectedRegex->isRequired = true;
        $expectedRegex->valuePerChannel = true;
        $expectedRegex->valuePerLocale = true;
        $expectedRegex->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => 'regular_expression',
            'regular_expression' => '/\w+/',
        ];

        $this->assertEquals($expectedRegex, $actualRegex);
    }

    private function assertLongDescriptionAttribute(array $attributeDetails): void
    {
        $actualLongDescription = $this->getAttributeWithCode($attributeDetails, 'long_description');

        $expectedLongDescription = new AttributeDetails();
        $expectedLongDescription->type = 'text';
        $expectedLongDescription->identifier = (string) $this->fixturesDesigner['attributes']['long_description']->getIdentifier();
        $expectedLongDescription->referenceEntityIdentifier = 'designer';
        $expectedLongDescription->code = 'long_description';
        $expectedLongDescription->labels = ['en_US' => 'Long description'];
        $expectedLongDescription->order = 5;
        $expectedLongDescription->isRequired = true;
        $expectedLongDescription->valuePerChannel = true;
        $expectedLongDescription->valuePerLocale = true;
        $expectedLongDescription->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => true,
            'is_rich_text_editor' => true,
            'validation_rule' => 'none',
            'regular_expression' => null,
        ];

        $this->assertEquals($expectedLongDescription, $actualLongDescription);
    }

    private function assertImageAttribute(array $attributeDetails): void
    {
        $actualImage = $this->getAttributeWithCode($attributeDetails, 'main_image');

        $expectedImage = new AttributeDetails();
        $expectedImage->type = 'image';
        $expectedImage->identifier = (string) $this->fixturesDesigner['attributes']['main_image']->getIdentifier();
        $expectedImage->referenceEntityIdentifier = 'designer';
        $expectedImage->code = 'main_image';
        $expectedImage->labels = ['en_US' => 'Portrait'];
        $expectedImage->order = 6;
        $expectedImage->isRequired = true;
        $expectedImage->valuePerChannel = true;
        $expectedImage->valuePerLocale = false;
        $expectedImage->additionalProperties = [
            'max_file_size' => '1000',
            'allowed_extensions' => ['png'],
        ];

        $this->assertEquals($expectedImage, $actualImage);
    }

    private function assertMainMaterialAttribute(AttributeDetails $attributeDetails): void
    {
        $expectedMainMaterial = new AttributeDetails();
        $expectedMainMaterial->type = 'option';
        $expectedMainMaterial->identifier = (string) $this->fixturesDesigner['attributes']['main_material']->getIdentifier();
        $expectedMainMaterial->referenceEntityIdentifier = 'designer';
        $expectedMainMaterial->code = 'main_material';
        $expectedMainMaterial->labels = ['en_US' => 'Main material'];
        $expectedMainMaterial->order = 7;
        $expectedMainMaterial->isRequired = false;
        $expectedMainMaterial->valuePerChannel = false;
        $expectedMainMaterial->valuePerLocale = false;
        $expectedMainMaterial->additionalProperties = [
            'options' => [],
        ];

        $this->assertEquals($expectedMainMaterial, $attributeDetails);
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
