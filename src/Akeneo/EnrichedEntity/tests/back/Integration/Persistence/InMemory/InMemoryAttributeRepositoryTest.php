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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\InMemory;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Common\InMemoryAttributeRepository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryAttributeRepositoryTest extends TestCase
{
    /** @var AttributeRepositoryInterface */
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
        $identifier = AttributeIdentifier::create('designer', 'name');
        $textAttribute = TextAttribute::create(
            $identifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155)
        );

        $this->attributeRepository->create($textAttribute);

        $attributeFound = $this->attributeRepository->getByIdentifier($identifier);
        Assert::isTrue($attributeFound->equals($textAttribute));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_attribute_with_the_same_identifier()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');
        $textAttribute = TextAttribute::create(
            $identifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155)
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
        $identifier = AttributeIdentifier::create('designer', 'name');
        $textAttribute = TextAttribute::create(
            $identifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155)
        );

        $this->attributeRepository->create($textAttribute);
        $textAttribute->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Nom']));
        $this->attributeRepository->update($textAttribute);

        $textAttribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::isTrue($textAttribute->equals($textAttribute));
        Assert::assertEquals($textAttribute->getLabel('fr_FR'), 'Nom');
    }

    /**
     * @test
     */
    public function it_throws_when_udpating_a_non_existing_attribute()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');
        $textAttribute = TextAttribute::create(
            $identifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155)
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
            $identifier = AttributeIdentifier::create('designer', 'name')
        );
    }
}
