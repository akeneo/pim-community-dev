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

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAttributeRepository;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryAttributeRepositoryTest extends TestCase
{
    private InMemoryAttributeRepository $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

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
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
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
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
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
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
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
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
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
    public function it_creates_an_attribute_with_and_finds_it_by_asset_family_and_attribute_code()
    {
        $attributeCode = AttributeCode::fromString('description');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $identifier = $this->attributeRepository->nextIdentifier($assetFamilyIdentifier, $attributeCode);
        $attribute = TextAttribute::createTextarea(
            $identifier,
            $assetFamilyIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
        $this->attributeRepository->create($attribute);
        $attributeFound = $this->attributeRepository->getByAssetFamilyAndCode('designer', 'description');
        $this->assertSame($attribute->normalize(), $attributeFound->normalize());
    }

    /**
     * @test
     */
    public function it_counts_attributes_by_asset_family()
    {
        $attributeCode = AttributeCode::fromString('description');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $this->assertSame(0, $this->attributeRepository->countByAssetFamily($assetFamilyIdentifier));

        $identifier = $this->attributeRepository->nextIdentifier($assetFamilyIdentifier, $attributeCode);
        $attribute = TextAttribute::createTextarea(
            $identifier,
            $assetFamilyIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'description', 'fr_FR' => 'description']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
        $this->attributeRepository->create($attribute);

        $this->assertSame(1, $this->attributeRepository->countByAssetFamily($assetFamilyIdentifier));

        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->attributeRepository->nextIdentifier($assetFamilyIdentifier, $attributeCode);
        $attribute = TextAttribute::createText(
            $identifier,
            $assetFamilyIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($attribute);

        $this->assertSame(2, $this->attributeRepository->countByAssetFamily($assetFamilyIdentifier));
    }

    /**
     * @test
     */
    public function it_throws_if_the_attribute_code_and_the_asset_family_are_not_found()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->attributeRepository->getByAssetFamilyAndCode('designer', 'description');
    }

    private function createAttributeWithIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        return TextAttribute::createText(
            $identifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }
}
