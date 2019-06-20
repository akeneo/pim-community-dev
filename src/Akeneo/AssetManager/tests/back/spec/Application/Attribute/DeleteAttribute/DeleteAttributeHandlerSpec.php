<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\DeleteAttribute;

use Akeneo\AssetManager\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsImageInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsLabelInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAttributeHandlerSpec extends ObjectBehavior
{
    public function let(
        FindAssetFamilyAttributeAsLabelInterface $findAssetFamilyAttributeAsLabel,
        FindAssetFamilyAttributeAsImageInterface $findAssetFamilyAttributeAsImage,
        AttributeRepositoryInterface $repository
    ) {
        $nameDesignerTest = TextAttribute::createText(
            AttributeIdentifier::fromString('name_designer_test'),
            AssetFamilyIdentifier::fromString('designer'),
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
            AssetFamilyIdentifier::fromString('designer'),
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
            AssetFamilyIdentifier::fromString('designer'),
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

        $findAssetFamilyAttributeAsLabel
            ->find(AssetFamilyIdentifier::fromString('designer'))
            ->willReturn(AttributeAsLabelReference::fromAttributeIdentifier(AttributeIdentifier::fromString('label')));
        $findAssetFamilyAttributeAsImage
            ->find(AssetFamilyIdentifier::fromString('designer'))
            ->willReturn(AttributeAsImageReference::fromAttributeIdentifier(AttributeIdentifier::fromString('image')));

        $this->beConstructedWith($findAssetFamilyAttributeAsLabel, $findAssetFamilyAttributeAsImage, $repository);
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

    function it_cannot_delete_an_attribute_when_used_as_attribute_as_label_of_the_asset_family(
        AttributeRepositoryInterface $repository
    ) {
        $command = new DeleteAttributeCommand(
            'label'
        );

        $identifier = AttributeIdentifier::fromString('label');
        $repository->deleteByIdentifier($identifier)->shouldNotBeCalled();


        $this->shouldThrow(\LogicException::class)->during('__invoke', [$command]);
    }

    function it_cannot_delete_an_attribute_when_used_as_attribute_as_image_of_the_asset_family(
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
