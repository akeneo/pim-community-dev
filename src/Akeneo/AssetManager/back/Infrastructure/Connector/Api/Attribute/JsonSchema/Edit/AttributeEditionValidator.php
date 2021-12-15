<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

final class AttributeEditionValidator
{
    /** @param $attributeValidators AttributeValidatorInterface[] */
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private GetAttributeIdentifierInterface $getAttributeIdentifier,
        private iterable $attributeValidators
    ) {
        Assert::allIsInstanceOf($attributeValidators, AttributeValidatorInterface::class);
    }

    /**
     * @throws \LogicException
     */
    public function validate(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        array $normalizedAttribute
    ): array {
        $attributeIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode($assetFamilyIdentifier, $attributeCode);
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);

        foreach ($this->attributeValidators as $attributeValidator) {
            if ($attributeValidator->support($attribute)) {
                return $attributeValidator->validate($normalizedAttribute);
            }
        }

        throw new \LogicException('No json schema validator found.');
    }
}
