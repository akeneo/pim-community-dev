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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ImageAttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\TextAttributeDetails;
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
    public function it_returns_the_attributes_details_for_an_reference_entity()
    {
        $attributeDetails = ($this->findAttributesDetails)(ReferenceEntityIdentifier::fromString('designer'));

        $this->assertCount(5, $attributeDetails);
        $this->assertNameAttribute($attributeDetails);
        $this->assertEmailAttribute($attributeDetails);
        $this->assertCustomRegex($attributeDetails);
        $this->assertLongDescriptionAttribute($attributeDetails);
        $this->assertImageAttribute($attributeDetails);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_the_reference_entity_does_not_have_any_attributes()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $attributeDetails = ($this->findAttributesDetails)($referenceEntityIdentifier);
        $this->assertCount(0, $attributeDetails);
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
            AttributeOrder::fromInteger(0),
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
            AttributeOrder::fromInteger(1),
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
            AttributeOrder::fromInteger(2),
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
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(4),
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
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $expectedName->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('name');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Name']);
        $expectedName->order = AttributeOrder::fromInteger(0);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextarea = AttributeIsTextarea::fromBoolean(false);
        $expectedName->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(false);
        $expectedName->validationRule = AttributeValidationRule::none();
        $expectedName->regularExpression = AttributeRegularExpression::createEmpty();
        $this->assertAttributeDetails($expectedName, $actualName);
    }

    private function assertEmailAttribute($attributeDetails): void
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'email');
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'email', 'test');
        $expectedName->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('email');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Email']);
        $expectedName->order = AttributeOrder::fromInteger(1);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextarea = AttributeIsTextarea::fromBoolean(false);
        $expectedName->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(false);
        $expectedName->validationRule = AttributeValidationRule::fromString(AttributeValidationRule::EMAIL);
        $expectedName->regularExpression = AttributeRegularExpression::createEmpty();
        $this->assertAttributeDetails($expectedName, $actualEmail);
    }

    private function assertCustomRegex($attributeDetails)
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'regex');
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'regex', 'test');
        $expectedName->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('regex');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Regex']);
        $expectedName->order = AttributeOrder::fromInteger(2);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextarea = AttributeIsTextarea::fromBoolean(false);
        $expectedName->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(false);
        $expectedName->validationRule = AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION);
        $expectedName->regularExpression = AttributeRegularExpression::fromString('/\w+/');
        $this->assertAttributeDetails($expectedName, $actualEmail);
    }

    private function assertLongDescriptionAttribute($attributeDetails)
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'long_description');
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'long_description', 'test');
        $expectedName->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('long_description');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Long description']);
        $expectedName->order = AttributeOrder::fromInteger(3);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextarea = AttributeIsTextarea::fromBoolean(true);
        $expectedName->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(true);
        $expectedName->validationRule = AttributeValidationRule::none();
        $expectedName->regularExpression = AttributeRegularExpression::createEmpty();
        $this->assertAttributeDetails($expectedName, $actualEmail);
    }

    /**
     * @param $attributeDetails
     *
     */
    private function assertImageAttribute($attributeDetails): void
    {
        $actualImage = $this->getAttributeWithCode($attributeDetails, 'image');
        $expectedImage = new ImageAttributeDetails();
        $expectedImage->identifier = AttributeIdentifier::create('designer', 'image', 'test');
        $expectedImage->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedImage->code = AttributeCode::fromString('name');
        $expectedImage->labels = LabelCollection::fromArray(['en_US' => 'Portrait']);
        $expectedImage->order = AttributeOrder::fromInteger(4);
        $expectedImage->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedImage->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedImage->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedImage->maxFileSize = AttributeMaxFileSize::fromString('1000');
        $expectedImage->allowedExtensions = AttributeAllowedExtensions::fromList(['pdf']);
        $this->assertAttributeDetails($expectedImage, $actualImage);
    }

    private function getAttributeWithCode(array $attributesDetails, string $attributeCode): AbstractAttributeDetails
    {
        foreach ($attributesDetails as $attributeDetails) {
            if ($attributeCode === (string) $attributeDetails->code) {
                return $attributeDetails;
            }
        }

        throw new \LogicException(sprintf('Attribute details with attribute code "%s" not found.', $attributeCode));
    }

    private function assertAttributeDetails(AbstractAttributeDetails $expected, AbstractAttributeDetails $actual)
    {
        $this->assertEquals($expected->identifier, $actual->identifier);
        $this->assertEquals($expected->referenceEntityIdentifier, $actual->referenceEntityIdentifier);
        $expectedLabels = $expected->labels->normalize();
        $actualLabels = $actual->labels->normalize();
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            )
        );
        $this->assertEquals($expected->order, $actual->order);
        $this->assertEquals($expected->isRequired, $actual->isRequired);
        $this->assertEquals($expected->valuePerChannel, $actual->valuePerChannel);
        $this->assertEquals($expected->valuePerLocale, $actual->valuePerLocale);
        if ($expected instanceof TextAttributeDetails && $actual instanceof TextAttributeDetails) {
            $this->assertEquals($expected->maxLength, $actual->maxLength);
            $this->assertEquals($expected->isTextarea, $actual->isTextarea);
            $this->assertEquals($expected->isRichTextEditor, $actual->isRichTextEditor);
            $this->assertEquals($expected->validationRule, $actual->validationRule);
            $this->assertEquals($expected->regularExpression, $actual->regularExpression);
        } elseif ($expected instanceof ImageAttributeDetails && $actual instanceof ImageAttributeDetails) {
            $this->assertEquals($expected->maxFileSize, $actual->maxFileSize);
            $this->assertSame($expected->allowedExtensions->normalize(), $actual->allowedExtensions->normalize());
        } else {
            throw new \LogicException('Expected attribute details to be of type text or image. unknown given');
        }
    }
}
