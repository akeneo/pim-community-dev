<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute;

use Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsImageInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAttributeHandlerSpec extends ObjectBehavior
{
    public function let(
        FindReferenceEntityAttributeAsLabelInterface $findReferenceEntityAttributeAsLabel,
        FindReferenceEntityAttributeAsImageInterface $findReferenceEntityAttributeAsImage,
        AttributeRepositoryInterface $repository
    ) {
        $nameDesignerTest = TextAttribute::createText(
            AttributeIdentifier::fromString('name_designer_test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name_designer_test'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $labelAttribute = TextAttribute::createText(
            AttributeIdentifier::fromString('label'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('label'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $mainImageAttribute = ImageAttribute::create(
            AttributeIdentifier::fromString('image'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );


        $repository->getByIdentifier(AttributeIdentifier::fromString('label'))->willReturn($labelAttribute);
        $repository->getByIdentifier(AttributeIdentifier::fromString('name_designer_test'))->willReturn($nameDesignerTest);
        $repository->getByIdentifier(AttributeIdentifier::fromString('image'))->willReturn($mainImageAttribute);

        $findReferenceEntityAttributeAsLabel
            ->__invoke(ReferenceEntityIdentifier::fromString('designer'))
            ->willReturn(AttributeAsLabelReference::fromAttributeIdentifier(AttributeIdentifier::fromString('label')));
        $findReferenceEntityAttributeAsImage
            ->__invoke(ReferenceEntityIdentifier::fromString('designer'))
            ->willReturn(AttributeAsImageReference::fromAttributeIdentifier(AttributeIdentifier::fromString('image')));

        $this->beConstructedWith($findReferenceEntityAttributeAsLabel, $findReferenceEntityAttributeAsImage, $repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteAttributeHandler::class);
    }

    function it_deletes_an_attribute_by_its_identifier(AttributeRepositoryInterface $repository)
    {
        $command = new DeleteAttributeCommand(
            'name_designer_test'
        );

        $identifier = AttributeIdentifier::fromString('name_designer_test');

        $repository->deleteByIdentifier($identifier)->shouldBeCalled();

        $this->__invoke($command);
    }

    function it_cannot_delete_an_attribute_when_used_as_attribute_as_label_of_the_reference_entity(
        AttributeRepositoryInterface $repository
    ) {
        $command = new DeleteAttributeCommand(
            'label'
        );

        $identifier = AttributeIdentifier::fromString('label');
        $repository->deleteByIdentifier($identifier)->shouldNotBeCalled();


        $this->shouldThrow(\LogicException::class)->during('__invoke', [$command]);
    }

    function it_cannot_delete_an_attribute_when_used_as_attribute_as_image_of_the_reference_entity(
        AttributeRepositoryInterface $repository
    ) {
        $command = new DeleteAttributeCommand(
            'image'
        );

        $identifier = AttributeIdentifier::fromString('image');
        $repository->deleteByIdentifier($identifier)->shouldNotBeCalled();


        $this->shouldThrow(\LogicException::class)->during('__invoke', [$command]);
    }
}
