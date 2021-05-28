<?php

namespace Akeneo\AssetManager\Application\Attribute\EditAttributeOption;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

class EditAttributeOptionHandler
{
    private GetAttributeIdentifierInterface $getAttributeIdentifier;

    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->attributeRepository = $attributeRepository;
    }

    public function __invoke(EditAttributeOptionCommand $command): void
    {
        $attributeIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            AttributeCode::fromString($command->attributeCode)
        );

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        Assert::isInstanceOfAny($attribute, [OptionCollectionAttribute::class, OptionAttribute::class]);
        Assert::true($attribute->hasAttributeOption(OptionCode::fromString($command->optionCode)));

        $option = $attribute->getAttributeOption(OptionCode::fromString($command->optionCode));
        $option->updateLabels(LabelCollection::fromArray($command->labels));

        $this->attributeRepository->update($attribute);
    }
}
