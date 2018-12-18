<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeOptionAction
{
    /** @var FindConnectorAttributeOptionInterface */
    private $findConnectorAttributeOptionQuery;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function __construct(
        FindConnectorAttributeOptionInterface $findConnectorAttributeOptionQuery,
        ReferenceEntityExistsInterface $referenceEntityExists
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorAttributeOptionQuery = $findConnectorAttributeOptionQuery;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier, string $attributeCode, string $optionCode): JsonResponse
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

        $attributeCode = AttributeCode::fromString($attributeCode);
        $optionCode = OptionCode::fromString($optionCode);

        $attributeOption = ($this->findConnectorAttributeOptionQuery)($referenceEntityIdentifier, $attributeCode, $optionCode);

        if (null === $attributeOption) {
            throw new NotFoundHttpException(sprintf('Attribute option "%s" does not exist for the attribute "%s".', $optionCode, $attributeCode));
        }

        $normalizedAttributeOption = $attributeOption->normalize();

        return new JsonResponse($normalizedAttributeOption);
    }
}
