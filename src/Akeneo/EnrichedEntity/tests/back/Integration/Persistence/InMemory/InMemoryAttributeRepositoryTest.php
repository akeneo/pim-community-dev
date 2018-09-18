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

namespace Akeneo\EnrichedEntity\Integration\Persistence\InMemory;

use Akeneo\EnrichedEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryAttributeRepositoryTest extends TestCase
{
    /** @var InMemoryAttributeRepository*/
    private $attributeRepository;

    public function setup()
    {
        $this->attributeRepository = new InMemoryAttributeRepository();
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_and_returns_it()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $textAttribute = TextAttribute::createText(
            $identifier,
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

    private function createAttributeWithIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        return TextAttribute::createText(
            $identifier,
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
    }
}
