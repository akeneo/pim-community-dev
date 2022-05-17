<?php

namespace Akeneo\AssetManager\Domain\Query\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

class AttributeSupportsOptions
{
    public function __construct(
        private GetAttributeIdentifierInterface $getAttributeIdentifier,
        private AttributeRepositoryInterface $attributeRepository
    ) {
    }

    public function supports(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): bool
    {
        $identifier = $this->getAttributeIdentifier->withAssetFamilyAndCode($assetFamilyIdentifier, $attributeCode);
        $attribute = $this->attributeRepository->getByIdentifier($identifier);

        return ($attribute instanceof OptionCollectionAttribute || $attribute instanceof OptionAttribute);
    }
}
