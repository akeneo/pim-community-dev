<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal\AddHalDownloadLinkToReferenceEntityImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorReferenceEntityAction
{
    /** @var FindConnectorReferenceEntityByReferenceEntityIdentifierInterface */
    private $findConnectorReferenceEntity;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var AddHalDownloadLinkToReferenceEntityImage */
    private $addHalLinksToReferenceEntityImage;

    public function __construct(
        FindConnectorReferenceEntityByReferenceEntityIdentifierInterface $findConnectorReferenceEntity,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AddHalDownloadLinkToReferenceEntityImage $addHalLinksToImageValues
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorReferenceEntity = $findConnectorReferenceEntity;
        $this->addHalLinksToReferenceEntityImage = $addHalLinksToImageValues;
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
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $normalizedReferenceEntity = $referenceEntity->normalize();
        $normalizedReferenceEntity = ($this->addHalLinksToReferenceEntityImage)($normalizedReferenceEntity);

        return new JsonResponse($normalizedReferenceEntity);
    }
}
