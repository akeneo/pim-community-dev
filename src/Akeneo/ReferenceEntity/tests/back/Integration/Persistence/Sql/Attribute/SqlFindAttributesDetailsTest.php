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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindAttributesDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindAttributesDetailsInterface */
    private $findAttributesDetails;

    public function setUp()
    {
        parent::setUp();

        $this->findAttributesDetails = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_attributes_details');
        $this->resetDB();
        $this->loadReferenceEntitiesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_details_for_a_reference_entity()
    {
        $attributeDetails = ($this->findAttributesDetails)(ReferenceEntityIdentifier::fromString('designer'));

        $this->assertCount(7, $attributeDetails);
        $this->assertNameAttribute($attributeDetails);
        $this->assertEmailAttribute($attributeDetails);
        $this->assertCustomRegex($attributeDetails);
        $this->assertLongDescriptionAttribute($attributeDetails);
        $this->assertImageAttribute($attributeDetails);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntitiesAndAttributes(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $referenceEntityFull = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntityFull);

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $email = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'email', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('email'),
            LabelCollection::fromArray(['en_US' => 'Email']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );
        $customRegex = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'regex', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('regex'),
            LabelCollection::fromArray(['en_US' => 'Regex']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );
        $longDescription = TextAttribute::createTextarea(
            AttributeIdentifier::create('designer', 'long_description', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('long_description'),
            LabelCollection::fromArray(['en_US' => 'Long description']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf'])
        );
        $attributesRepository->create($name);
        $attributesRepository->create($email);
        $attributesRepository->create($customRegex);
        $attributesRepository->create($longDescription);
        $attributesRepository->create($imageAttribute);

        $referenceEntityEmpty = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntityEmpty);
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
        $expectedName->identifier = 'name_designer_test';
        $expectedName->referenceEntityIdentifier = 'designer';
        $expectedName->code = 'name';
        $expectedName->labels = ['en_US' => 'Name'];
        $expectedName->order = 2;
        $expectedName->isRequired = true;
        $expectedName->valuePerChannel = true;
        $expectedName->valuePerLocale = true;
        $expectedName->additionalProperties = [
            'max_length' => 155,
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
        $expectedEmail->identifier = 'email_designer_test';
        $expectedEmail->referenceEntityIdentifier = 'designer';
        $expectedEmail->code = 'email';
        $expectedEmail->labels = ['en_US' => 'Email'];
        $expectedEmail->order = 3;
        $expectedEmail->isRequired = true;
        $expectedEmail->valuePerChannel = true;
        $expectedEmail->valuePerLocale = true;
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
        $expectedRegex->identifier = 'regex_designer_test';
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

    private function assertLongDescriptionAttribute($attributeDetails)
    {
        $actualLongDescription = $this->getAttributeWithCode($attributeDetails, 'long_description');

        $expectedLongDescription = new AttributeDetails();
        $expectedLongDescription->type = 'text';
        $expectedLongDescription->identifier = 'long_description_designer_test';
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

    /**
     * @param $attributeDetails
     *
     */
    private function assertImageAttribute($attributeDetails): void
    {
        $actualImage = $this->getAttributeWithCode($attributeDetails, 'main_image');

        $expectedImage = new AttributeDetails();
        $expectedImage->type = 'image';
        $expectedImage->identifier = 'image_designer_test';
        $expectedImage->referenceEntityIdentifier = 'designer';
        $expectedImage->code = 'main_image';
        $expectedImage->labels = ['en_US' => 'Portrait'];
        $expectedImage->order = 6;
        $expectedImage->isRequired = true;
        $expectedImage->valuePerChannel = true;
        $expectedImage->valuePerLocale = true;
        $expectedImage->additionalProperties = [
            'max_file_size' => '1000',
            'allowed_extensions' => ['pdf'],
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
