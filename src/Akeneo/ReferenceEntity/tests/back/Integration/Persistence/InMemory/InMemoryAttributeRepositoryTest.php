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

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeNotFoundException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryAttributeRepositoryTest extends TestCase
{
    /** @var InMemoryAttributeRepository*/
    private $attributeRepository;

    public function setup()
    {
        $this->attributeRepository = new InMemoryAttributeRepository(new EventDispatcher());
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_and_returns_it()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $textAttribute = TextAttribute::createText(
            $identifier,
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

        $this->attributeRepository->create($textAttribute);

        $attributeFound = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertTrue($attributeFound->equals($textAttribute));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_attribute_with_the_same_identifier()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $textAttribute = TextAttribute::createText(
            $identifier,
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

        $this->attributeRepository->create($textAttribute);

        $this->expectException(\RuntimeException::class);
        $this->attributeRepository->create($textAttribute);
    }

    /**
     * @test
     */
    public function it_updates_an_attribute_and_returns_it()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $textAttribute = TextAttribute::createText(
            $identifier,
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

        $this->attributeRepository->create($textAttribute);
        $textAttribute->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Nom']));
        $this->attributeRepository->update($textAttribute);

        $textAttribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertTrue($textAttribute->equals($textAttribute));
        Assert::assertEquals($textAttribute->getLabel('fr_FR'), 'Nom');
    }

    /**
     * @test
     */
    public function it_throws_when_udpating_a_non_existing_attribute()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $textAttribute = TextAttribute::createText(
            $identifier,
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

        $this->expectException(\RuntimeException::class);
        $this->attributeRepository->update($textAttribute);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByIdentifier(
            $identifier = AttributeIdentifier::create('designer', 'name', 'test')
        );
    }

    /**
     * @test
     */
    public function it_deletes_an_attribute_by_its_identifier()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $textAttribute = $this->createAttributeWithIdentifier($identifier);
        $this->attributeRepository->create($textAttribute);

        $this->attributeRepository->deleteByIdentifier($identifier);

        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_with_and_finds_it_by_reference_entity_and_attribute_code()
    {
        $attributeCode = AttributeCode::fromString('description');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $identifier = $this->attributeRepository->nextIdentifier($referenceEntityIdentifier, $attributeCode);
        $attribute = TextAttribute::createTextarea(
            $identifier,
            $referenceEntityIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
        $this->attributeRepository->create($attribute);
        $attributeFound = $this->attributeRepository->getByReferenceEntityAndCode('designer', 'description');
        $this->assertSame($attribute->normalize(), $attributeFound->normalize());
    }

    /**
     * @test
     */
    public function it_counts_attributes_by_reference_entity()
    {
        $attributeCode = AttributeCode::fromString('description');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $this->assertSame(0, $this->attributeRepository->countByReferenceEntity($referenceEntityIdentifier));

        $identifier = $this->attributeRepository->nextIdentifier($referenceEntityIdentifier, $attributeCode);
        $attribute = TextAttribute::createTextarea(
            $identifier,
            $referenceEntityIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'description', 'fr_FR' => 'description']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
        $this->attributeRepository->create($attribute);

        $this->assertSame(1, $this->attributeRepository->countByReferenceEntity($referenceEntityIdentifier));

        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->attributeRepository->nextIdentifier($referenceEntityIdentifier, $attributeCode);
        $attribute = TextAttribute::createText(
            $identifier,
            $referenceEntityIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($attribute);

        $this->assertSame(2, $this->attributeRepository->countByReferenceEntity($referenceEntityIdentifier));
    }

    /**
     * @test
     */
    public function it_throws_if_the_attribute_code_and_the_reference_entity_are_not_found()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->attributeRepository->getByReferenceEntityAndCode('designer', 'description');
    }

    private function createAttributeWithIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        return TextAttribute::createText(
            $identifier,
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
    }
}
