<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Attribute\AppendAttributeOption;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

class AppendAttributeOptionHandler
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

    public function __invoke(AppendAttributeOptionCommand $command): void
    {
        $attributeIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            AttributeCode::fromString($command->attributeCode)
        );

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        Assert::isInstanceOfAny($attribute, [OptionCollectionAttribute::class, OptionAttribute::class]);
        Assert::false($attribute->hasAttributeOption(OptionCode::fromString($command->optionCode)));

        $option = AttributeOption::create(
            OptionCode::fromString($command->optionCode),
            LabelCollection::fromArray($command->labels)
        );

        $attribute->addOption($option);
        $this->attributeRepository->update($attribute);
    }
}
