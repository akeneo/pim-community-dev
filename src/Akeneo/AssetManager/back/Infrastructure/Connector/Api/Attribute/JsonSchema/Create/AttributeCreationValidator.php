<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Webmozart\Assert\Assert;

final class AttributeCreationValidator
{
    /** @var AttributeValidatorInterface[] */
    private iterable $attributeValidator;

    public function __construct(iterable $attributeValidators)
    {
        Assert::allIsInstanceOf($attributeValidators, AttributeValidatorInterface::class);
        $this->attributeValidator = $attributeValidators;
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    public function validate(array $normalizedAttribute): array
    {
        if (!isset($normalizedAttribute['type'])) {
            throw new UnprocessableEntityHttpException('Attribute type is mandatory.');
        }

        if (!is_string($normalizedAttribute['type'])) {
            throw new UnprocessableEntityHttpException(sprintf('Attribute type "%s" should be a string.', $normalizedAttribute['type']));
        }

        foreach ($this->attributeValidator as $attributeValidator) {
            if (in_array($normalizedAttribute['type'], $attributeValidator->forAttributeTypes())) {
                return $attributeValidator->validate($normalizedAttribute);
            }
        }

        throw new UnprocessableEntityHttpException(sprintf('Attribute type "%s" is unknown.', $normalizedAttribute['type']));
    }
}
