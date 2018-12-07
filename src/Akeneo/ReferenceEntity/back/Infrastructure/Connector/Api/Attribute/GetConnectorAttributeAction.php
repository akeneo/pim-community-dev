<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\Hal\AddHalSelfLinkToNormalizedConnectorAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeAction
{
    /** @var FindConnectorAttributeByIdentifierAndCodeInterface */
    private $findConnectorAttributeQuery;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function __construct(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttributeQuery,
        ReferenceEntityExistsInterface $referenceEntityExists
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorAttributeQuery = $findConnectorAttributeQuery;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $code, string $referenceEntityIdentifier): JsonResponse
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

        $attributeCode = AttributeCode::fromString($code);
        $attribute = ($this->findConnectorAttributeQuery)($referenceEntityIdentifier, $attributeCode);

        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist for the reference entity "%s".', $code, $referenceEntityIdentifier));
        }

        $normalizedAttribute = $attribute->normalize();

        return new JsonResponse($normalizedAttribute);
    }
}
