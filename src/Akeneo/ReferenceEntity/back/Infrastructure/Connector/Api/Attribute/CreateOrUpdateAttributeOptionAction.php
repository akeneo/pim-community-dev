<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CreateOrUpdateAttributeOptionAction
{

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $attributeCode, string $optionCode)
    {
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $referenceEntityExists = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);

        if (false === $referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        try {
            $attributeCode = AttributeCode::fromString($attributeCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeExists = $this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for reference entity "%s".',
                (string) $attributeCode,
                (string) $referenceEntityIdentifier
            ));
        }

        $attributeSupportsOptions = ($this->attributeSupportsOptions)($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeSupportsOptions) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not support options.', $attributeCode));
        }

        $attribute = ($this->findConnectorAttributeQuery)($referenceEntityIdentifier, $attributeCode);

        try {
            $optionCode = OptionCode::fromString($optionCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $optionExists = $attribute->hasAttributeOption($optionCode);

        $optionExists ?
            $this->editOption($referenceEntityIdentifier, $attributeCode, $optionCode, $request) :
            $this->createOption($referenceEntityIdentifier, $attributeCode, $optionCode, $request);

    }

    public function editOption(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        Request $request
    )
    {
        // Validate the data with json schema validator
        // Use the handler to update
        // AppendAttributeOptionCommand
        // AppendAttributeOptionHandler
        // Normalize the attribute
    }

    public function createOption(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        Request $request
    )
    {

    }
}
