<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;

class InMemoryFindValueKeysByAttributeType implements FindValueKeysByAttributeTypeInterface
{
    private InMemoryAttributeRepository $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $attributeTypes): array
    {
        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);
        $valueKeys = [];

        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (in_array($attribute->normalize()['type'], $attributeTypes)) {
                $valueKeys[] = (string) $attribute->getIdentifier();
            }
        }

        return $valueKeys;
    }
}
