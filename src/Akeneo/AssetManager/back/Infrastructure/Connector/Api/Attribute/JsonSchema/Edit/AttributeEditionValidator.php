<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

final class AttributeEditionValidator
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    /** @var AttributeValidatorInterface[] */
    private $attributeValidator;

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
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        array $normalizedAttribute
    ): array {
        $attributeIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);

        foreach ($this->attributeValidator as $attributeValidator) {
            if ($attributeValidator->support($attribute)) {
                return $attributeValidator->validate($normalizedAttribute);
            }
        }

        throw new \LogicException(sprintf('No json schema validator found.'));
    }
}
