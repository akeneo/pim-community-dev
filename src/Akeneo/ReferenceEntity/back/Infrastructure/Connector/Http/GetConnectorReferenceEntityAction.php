<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;


use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal\AddHalDownloadLinkToImages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorReferenceEntityAction
{
    /** @var FindConnectorReferenceEntityByReferenceEntityIdentifierInterface */
    private $findConnectorReferenceEntity;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var AddHalDownloadLinkToImages */
    private $addHalLinksToImageValues;

    public function __construct(
        FindConnectorReferenceEntityByReferenceEntityIdentifierInterface $findConnectorReferenceEntity,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AddHalDownloadLinkToImages $addHalLinksToImageValues
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorReferenceEntity = $findConnectorReferenceEntity;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
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

        $referenceEntity = ($this->findConnectorReferenceEntity)($referenceEntityIdentifier);

        if (null === $referenceEntity) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier, $referenceEntityIdentifier));
        }

        $normalizedReferenceEntity = $referenceEntity->normalize();

        return new JsonResponse($normalizedReferenceEntity);
    }
}
