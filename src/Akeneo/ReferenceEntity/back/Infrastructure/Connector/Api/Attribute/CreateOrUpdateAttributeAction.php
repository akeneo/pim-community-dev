<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAttributeAction
{
    public function __invoke(string $referenceEntityIdentifier, string $attributeIdentifier): Response
    {
        return new JsonResponse();
    }
}
