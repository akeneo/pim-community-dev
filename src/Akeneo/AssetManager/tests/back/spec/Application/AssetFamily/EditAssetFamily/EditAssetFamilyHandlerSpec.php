<?php

namespace spec\Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCollectionFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\ClockInterface;
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
        FileExistsInterface $fileExists,
        TransformationCollectionFactory $transformationCollectionFactory,
        ClockInterface $clock
    ) {
        $this->beConstructedWith($repository, $getAttributeIdentifier, $storer, $fileExists, $transformationCollectionFactory, $clock);
        $clock->now()->willReturn(new \DateTime('2000-01-01'));
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

        $assetFamily->updateImage(Argument::that(fn($image) => $image instanceof Image && $image->isEmpty()))
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

        $assetFamily->updateImage(Argument::that(fn($image) => $image instanceof Image && $image->getKey() === '/path/image.jpg'))
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

        $assetFamily->updateImage(Argument::that(fn($image) => $image instanceof Image && $image->getKey() === '/path/image.jpg'))
            ->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $fileExists->exists('/path/image.jpg')->willReturn(true);

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_an_attribute_as_main_media(
        AssetFamilyRepositoryInterface $repository,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AssetFamily $assetFamily,
        EditAssetFamilyCommand $editAssetFamilyCommand,
        Image $image,
        AttributeIdentifier $attributeAsMainMediaIdentifier
    ) {
        $editAssetFamilyCommand->identifier = 'designer';
        $editAssetFamilyCommand->labels = [];
        $editAssetFamilyCommand->productLinkRules = [];
        $editAssetFamilyCommand->attributeAsMainMedia = 'new_attribute';

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(Argument::that(fn($image) => $image instanceof Image && $image->isEmpty()))->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('new_attribute')
        )->willReturn($attributeAsMainMediaIdentifier);

        $assetFamily->updateAttributeAsMainMediaReference(
            AttributeAsMainMediaReference::fromAttributeIdentifier($attributeAsMainMediaIdentifier->getWrappedObject())
        )->shouldBeCalled();
        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_an_empty_attribute_as_main_media(
        AssetFamilyRepositoryInterface $repository,
        AssetFamily $assetFamily,
        EditAssetFamilyCommand $editAssetFamilyCommand,
        Image $image
    ) {
        $editAssetFamilyCommand->identifier = 'designer';
        $editAssetFamilyCommand->labels = [];
        $editAssetFamilyCommand->productLinkRules = [];
        $editAssetFamilyCommand->attributeAsMainMedia = null;

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(Argument::that(fn($image) => $image instanceof Image && $image->isEmpty()))
            ->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateAttributeAsMainMediaReference(Argument::type(AttributeAsMainMediaReference::class))
            ->shouldNotBeCalled();

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_transformations(
        AssetFamilyRepositoryInterface $repository,
        TransformationCollectionFactory $transformationCollectionFactory,
        AssetFamily $assetFamily,
        Image $image,
        TransformationCollection $currentTransformationCollection,
        TransformationCollection $newTransformationCollection
    ) {
        $normalizedTransformations = [
            [
                'label' => 'label',
                'source' => [
                    'attribute' => 'main',
                    'locale' => null,
                    'channel' => null,
                ],
                'target' => [
                    'attribute' => 'target',
                    'locale' => null,
                    'channel' => null,
                ],
                [
                    'operations' => [
                        [
                            'type' => 'scale',
                            'parameters' => [
                                'ratio' => 50,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            'packshot',
            ['en_US' => 'Packshots'],
            null,
            null,
            [],
            $normalizedTransformations,
            null
        );

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(
            Argument::that(fn($image) => $image instanceof Image && $image->isEmpty())
        )->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
            ->shouldBeCalled();
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('id');
        $assetFamily->getIdentifier()->willReturn($assetFamilyIdentifier);
        foreach ($normalizedTransformations as &$normalizedTransformation) {
            $normalizedTransformation['updated_at'] = (new \DateTime('2000-01-01'))->format(\DateTimeInterface::ISO8601);
        }
        $transformationCollectionFactory->fromNormalized($normalizedTransformations)
            ->willReturn($newTransformationCollection);
        $assetFamily->getTransformationCollection()->willReturn($currentTransformationCollection);
        $currentTransformationCollection->update($newTransformationCollection)->shouldBeCalledOnce();

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_edits_an_asset_family_with_naming_convention(
        AssetFamilyRepositoryInterface $repository,
        TransformationCollectionFactory $transformationCollectionFactory,
        AssetFamily $assetFamily,
        Image $image,
        TransformationCollection $currentTransformationCollection,
        TransformationCollection $newTransformationCollection
    ) {
        $normalizedNamingConvention = [
            'source' => [
                'property' => 'code',
                'channel' => null,
                'locale' => null,
            ],
            'pattern' => '/valid_pattern/',
            'abort_asset_creation_on_error' => true
        ];

        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            'packshot',
            ['en_US' => 'Packshots'],
            null,
            null,
            null,
            null,
            $normalizedNamingConvention
        );

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($assetFamily);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $assetFamily->updateImage(
            Argument::that(fn($image) => $image instanceof Image && $image->isEmpty())
        )->shouldBeCalled();

        $assetFamily->updateNamingConvention(
            NamingConvention::createFromNormalized($normalizedNamingConvention)
        )->shouldBeCalled();

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }

    function it_does_not_edit_transformations_if_they_are_not_provided(
        AssetFamilyRepositoryInterface $repository,
        TransformationCollectionFactory $transformationCollectionFactory,
        AssetFamily $assetFamily,
        Image $image,
        TransformationCollection $transformations
    ) {
        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            'packshot',
            ['en_US' => 'Packshots'],
            null,
            null,
            [],
            null,
            null
        );

        $repository->getByIdentifier(Argument::type(AssetFamilyIdentifier::class))
                   ->willReturn($assetFamily);

        $assetFamily->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $assetFamily->updateLabels(Argument::type(LabelCollection::class))
                    ->shouldBeCalled();

        $assetFamily->updateImage(
            Argument::that(
                fn($image) => $image instanceof Image && $image->isEmpty()
            )
        )->shouldBeCalled();

        $assetFamily->updateRuleTemplateCollection(Argument::type(RuleTemplateCollection::class))
                    ->shouldBeCalled();
        $transformationCollectionFactory->fromNormalized(Argument::any())->shouldNotBeCalled();
        $assetFamily->withTransformationCollection(Argument::any())->shouldNotBeCalled();

        $repository->update($assetFamily)->shouldBeCalled();

        $this->__invoke($editAssetFamilyCommand);
    }
}
