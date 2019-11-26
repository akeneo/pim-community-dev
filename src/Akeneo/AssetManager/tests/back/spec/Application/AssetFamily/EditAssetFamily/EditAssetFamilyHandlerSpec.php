<?php

namespace spec\Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\File\FileExistsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditAssetFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        AssetFamilyRepositoryInterface $repository,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        FileStorerInterface $storer,
        FileExistsInterface $fileExists
    ) {
        $this->beConstructedWith($repository, $getAttributeIdentifier, $storer, $fileExists);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAssetFamilyHandler::class);
    }

    function it_edits_an_asset_family_with_an_empty_image(
        AssetFamilyRepositoryInterface $repository,
        AssetFamily $assetFamily,
        EditAssetFamilyCommand $editAssetFamilyCommand,
        Image $image
    ) {
        $editAssetFamilyCommand->identifier = 'designer';
        $editAssetFamilyCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];
        $editAssetFamilyCommand->image = null;
        $editAssetFamilyCommand->productLinkRules = [
            [
                'product_selections' => [
                    [
                        'field'    => 'sku',
                        'operator' => '=',
                        'value'    => '{{product_sku}}'
                    ]
                ],
                'assign_assets_to' => [
                    [
                        'mode'      => 'add',
                        'attribute' => '{{attribute}}'
                    ]
                ]
            ]
        ];

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(Argument::that(function ($image) {
                return $image instanceof Image && $image->isEmpty();
            }))
            ->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_an_uploaded_image(
        AssetFamilyRepositoryInterface $repository,
        AssetFamily $assetFamily,
        EditAssetFamilyCommand $editAssetFamilyCommand,
        FileStorerInterface $storer,
        FileInfoInterface $fileInfo,
        Image $image,
        FileExistsInterface $fileExists
    ) {
        $editAssetFamilyCommand->identifier = 'designer';
        $editAssetFamilyCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];
        $editAssetFamilyCommand->image = ['originalFilename' => 'image.jpg', 'filePath' => '/path/image.jpg'];
        $editAssetFamilyCommand->productLinkRules = [
            [
                'product_selections' => [
                    [
                        'field'    => 'sku',
                        'operator' => '=',
                        'value'    => '{{product_sku}}'
                    ]
                ],
                'assign_assets_to' => [
                    [
                        'mode'      => 'add',
                        'attribute' => '{{attribute}}'
                    ]
                ]
            ]
        ];

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(Argument::that(function ($image) {
                return $image instanceof Image && $image->getKey() === '/path/image.jpg';
            }))
            ->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $fileExists->exists('/path/image.jpg')->willReturn(false);

        $storer->store(Argument::type(\SplFileInfo::class), Argument::type('string'))
            ->willReturn($fileInfo);

        $fileInfo->getKey()
            ->willReturn('/path/image.jpg');

        $fileInfo->getOriginalFilename()
            ->willReturn('image.jpg');

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_a_stored_image(
        AssetFamilyRepositoryInterface $repository,
        AssetFamily $assetFamily,
        EditAssetFamilyCommand $editAssetFamilyCommand,
        Image $image,
        FileExistsInterface $fileExists
    ) {
        $editAssetFamilyCommand->identifier = 'designer';
        $editAssetFamilyCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];
        $editAssetFamilyCommand->image = ['originalFilename' => 'image.jpg', 'filePath' => '/path/image.jpg'];
        $editAssetFamilyCommand->productLinkRules = [
            [
                'product_selections' => [
                    [
                        'field'    => 'sku',
                        'operator' => '=',
                        'value'    => '{{product_sku}}'
                    ]
                ],
                'assign_assets_to' => [
                    [
                        'mode'      => 'add',
                        'attribute' => '{{attribute}}'
                    ]
                ]
            ]
        ];

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(Argument::that(function ($image) {
                return $image instanceof Image && $image->getKey() === '/path/image.jpg';
            }))
            ->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $fileExists->exists('/path/image.jpg')->willReturn(true);

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_an_attribute_as_image(
        AssetFamilyRepositoryInterface $repository,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AssetFamily $assetFamily,
        EditAssetFamilyCommand $editAssetFamilyCommand,
        Image $image,
        AttributeIdentifier $attributeAsImageIdentifier
    ) {
        $editAssetFamilyCommand->identifier = 'designer';
        $editAssetFamilyCommand->labels = [];
        $editAssetFamilyCommand->productLinkRules = [];
        $editAssetFamilyCommand->attributeAsImage = 'new_attribute';

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(Argument::that(function ($image) {
            return $image instanceof Image && $image->isEmpty();
        }))->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('new_attribute')
        )->willReturn($attributeAsImageIdentifier);

        $assetFamily->updateAttributeAsImageReference(
            AttributeAsImageReference::fromAttributeIdentifier($attributeAsImageIdentifier->getWrappedObject())
        )->shouldBeCalled();

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_an_empty_attribute_as_image(
        AssetFamilyRepositoryInterface $repository,
        AssetFamily $assetFamily,
        EditAssetFamilyCommand $editAssetFamilyCommand,
        Image $image
    ) {
        $editAssetFamilyCommand->identifier = 'designer';
        $editAssetFamilyCommand->labels = [];
        $editAssetFamilyCommand->productLinkRules = [];
        $editAssetFamilyCommand->attributeAsImage = null;

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(Argument::that(function ($image) {
            return $image instanceof Image && $image->isEmpty();
        }))
            ->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateAttributeAsImageReference(Argument::type(AttributeAsImageReference::class))
            ->shouldNotBeCalled();

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }
}
