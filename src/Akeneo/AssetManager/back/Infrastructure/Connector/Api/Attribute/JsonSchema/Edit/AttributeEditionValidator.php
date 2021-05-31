<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

final class AttributeEditionValidator
{
    private AttributeRepositoryInterface $attributeRepository;

    private GetAttributeIdentifierInterface $getAttributeIdentifier;

    /** @var AttributeValidatorInterface[] */
    private iterable $attributeValidator;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        iterable $attributeValidators
    ) {
        Assert::allIsInstanceOf($attributeValidators, AttributeValidatorInterface::class);
        $this->attributeRepository = $attributeRepository;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->attributeValidator = $attributeValidators;
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

        foreach ($this->attributeValidator as $attributeValidator) {
            if ($attributeValidator->support($attribute)) {
                return $attributeValidator->validate($normalizedAttribute);
            }
        }

        throw new \LogicException('No json schema validator found.');
    }
}
