<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Symfony\Component\HttpFoundation\Request;

class CreateOrUpdateAttributeOptionAction
{

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $attributeCode, string $optionCode)
    {
        // Does reference entity exist

        // Does attribute exist

        // AttributeSupportsOptions? if not return an error

        // Does option exist ?
            // Create or update
    }

    public function editOption(string $referenceEntityIdentifier, string $attributeCode, string $optionCode)
    {
        // Validate the data with json schema validator
        // Use the handler to update
        // AppendAttributeOptionCommand
        // AppendAttributeOptionHandler
        // Normalize the attribute
    }

    public function createOption(string $referenceEntityIdentifier, string $attributeCode, string $optionCode)
    {

    }
}
