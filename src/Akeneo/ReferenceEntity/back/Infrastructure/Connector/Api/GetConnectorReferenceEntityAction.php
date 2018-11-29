<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Hal\AddHalDownloadLinkToReferenceEntityImage;
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
    public function __invoke(string $code): JsonResponse
    {
        try {
            $code = ReferenceEntityIdentifier::fromString($code);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $referenceEntity = ($this->findConnectorReferenceEntity)($code);

        if (null === $referenceEntity) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $code));
        }

        $normalizedReferenceEntity = $referenceEntity->normalize();
        $normalizedReferenceEntity = ($this->addHalLinksToReferenceEntityImage)($normalizedReferenceEntity);

        return new JsonResponse($normalizedReferenceEntity);
    }
}
