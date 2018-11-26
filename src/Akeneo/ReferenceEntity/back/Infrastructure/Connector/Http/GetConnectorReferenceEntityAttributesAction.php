<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorReferenceEntityAttributesAction
{
    /** @var FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface */
    private $findConnectorReferenceEntityAttributes;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;


    public function __construct(
        FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface $findConnectorReferenceEntityAttributes,
        ReferenceEntityExistsInterface $referenceEntityExists
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorReferenceEntityAttributes = $findConnectorReferenceEntityAttributes;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier): JsonResponse
    {
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        // @TODO - Check if reference entity exists first
        $attributes = ($this->findConnectorReferenceEntityAttributes)($referenceEntityIdentifier);

//        if (null === $referenceEntity) {
//            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
//        }
//
//        $normalizedReferenceEntity = $referenceEntity->normalize();

        $normalizedAttributes = [];

        foreach ($attributes as $attribute) {
            $normalizedAttributes[] = $attribute->normalize();
        }

        return new JsonResponse($attributes);
    }
}
