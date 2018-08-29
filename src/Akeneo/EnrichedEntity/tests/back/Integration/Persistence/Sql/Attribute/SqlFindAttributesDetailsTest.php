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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ImageAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\TextAttributeDetails;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindAttributesDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindAttributesDetailsInterface */
    private $findAttributesDetails;

    public function setUp()
    {
        parent::setUp();

        $this->findAttributesDetails = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_attributes_details');
        $this->resetDB();
        $this->loadEnrichedEntitiesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_details_for_an_enriched_entity()
    {
        $attributeDetails = ($this->findAttributesDetails)(EnrichedEntityIdentifier::fromString('designer'));

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
    public function it_returns_an_empty_array_if_the_enriched_entity_does_not_have_any_attributes()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('brand');
        $attributeDetails = ($this->findAttributesDetails)($enrichedEntityIdentifier);
        $this->assertCount(0, $attributeDetails);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntitiesAndAttributes(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $attributesRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');

        $enrichedEntityFull = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntityFull);

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
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
            AttributeIdentifier::create('designer', 'email'),
            EnrichedEntityIdentifier::fromString('designer'),
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
            AttributeIdentifier::create('designer', 'regex'),
            EnrichedEntityIdentifier::fromString('designer'),
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
        $longDescription = TextAttribute::createTextArea(
            AttributeIdentifier::create('designer', 'long_description'),
            EnrichedEntityIdentifier::fromString('designer'),
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
            AttributeIdentifier::create('designer', 'image'),
            EnrichedEntityIdentifier::fromString('designer'),
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

        $enrichedEntityEmpty = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntityEmpty);
    }

    /**
     * @param $attributeDetails
     *
     */
    private function assertNameAttribute($attributeDetails): void
    {
        $actualName = $this->getAttributeWithCode($attributeDetails, 'name');
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'name');
        $expectedName->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('name');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Name']);
        $expectedName->order = AttributeOrder::fromInteger(0);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextArea = AttributeIsTextarea::fromBoolean(false);
        $expectedName->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(false);
        $expectedName->validationRule = AttributeValidationRule::none();
        $expectedName->regularExpression = AttributeRegularExpression::createEmpty();
        $this->assertAttributeDetails($expectedName, $actualName);
    }

    private function assertEmailAttribute($attributeDetails): void
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'email');
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'email');
        $expectedName->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('email');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Email']);
        $expectedName->order = AttributeOrder::fromInteger(1);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextArea = AttributeIsTextarea::fromBoolean(false);
        $expectedName->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(false);
        $expectedName->validationRule = AttributeValidationRule::fromString(AttributeValidationRule::EMAIL);
        $expectedName->regularExpression = AttributeRegularExpression::createEmpty();
        $this->assertAttributeDetails($expectedName, $actualEmail);
    }

    private function assertCustomRegex($attributeDetails)
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'regex');
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'regex');
        $expectedName->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('regex');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Regex']);
        $expectedName->order = AttributeOrder::fromInteger(2);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextArea = AttributeIsTextarea::fromBoolean(false);
        $expectedName->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(false);
        $expectedName->validationRule = AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION);
        $expectedName->regularExpression = AttributeRegularExpression::fromString('/\w+/');
        $this->assertAttributeDetails($expectedName, $actualEmail);
    }

    private function assertLongDescriptionAttribute($attributeDetails)
    {
        $actualEmail = $this->getAttributeWithCode($attributeDetails, 'long_description');
        $expectedName = new TextAttributeDetails();
        $expectedName->identifier = AttributeIdentifier::create('designer', 'long_description');
        $expectedName->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedName->code = AttributeCode::fromString('long_description');
        $expectedName->labels = LabelCollection::fromArray(['en_US' => 'Long description']);
        $expectedName->order = AttributeOrder::fromInteger(3);
        $expectedName->isRequired = AttributeIsRequired::fromBoolean(true);
        $expectedName->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $expectedName->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $expectedName->maxLength = AttributeMaxLength::fromInteger(155);
        $expectedName->isTextArea = AttributeIsTextarea::fromBoolean(true);
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
        $expectedImage->identifier = AttributeIdentifier::create('designer', 'image');
        $expectedImage->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
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
        $this->assertEquals($expected->enrichedEntityIdentifier, $actual->enrichedEntityIdentifier);
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
            $this->assertEquals($expected->isTextArea, $actual->isTextArea);
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
