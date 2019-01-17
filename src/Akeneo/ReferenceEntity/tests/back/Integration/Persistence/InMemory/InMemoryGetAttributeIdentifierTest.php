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
use Akeneo\ReferenceEntity\Common\Fake\InMemoryGetAttributeIdentifier;
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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeNotFoundException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryGetAttributeIdentifierTest extends TestCase
{
    /** @var InMemoryAttributeRepository*/
    private $attributeRepository;

    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    public function setup()
    {
        $this->attributeRepository = new InMemoryAttributeRepository(new EventDispatcher());
        $this->getAttributeIdentifier = new InMemoryGetAttributeIdentifier($this->attributeRepository);
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_attribute_exists_for_the_given_identifier()
    {
        $identifier = $this->createAttribute(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            AttributeIdentifier::fromString('designer_name')
        );

        $attributeIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('designer'), AttributeCode::fromString('name')
        );
        Assert::assertEquals($identifier, $attributeIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_attribute_identifier_does_not_exist_for_the_given_reference_entity_identifier_and_attribute_code()
    {
        $this->expectException(\LogicException::class);
        $this->getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('designer'), AttributeCode::fromString('name')
        );
    }

    private function createAttribute(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        AttributeIdentifier $identifier
    ): AttributeIdentifier {
        $textAttribute = TextAttribute::createText(
            $identifier,
            $referenceEntityIdentifier,
            $attributeCode,
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

        return $textAttribute->getIdentifier();
    }
}
