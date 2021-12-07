<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\Hal\AddHalDownloadLinkToReferenceEntityImage;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorReferenceEntityAction
{
    private FindConnectorReferenceEntityByReferenceEntityIdentifierInterface $findConnectorReferenceEntity;
    private AddHalDownloadLinkToReferenceEntityImage $addHalLinksToReferenceEntityImage;
    private SecurityFacade $securityFacade;

    public function __construct(
        FindConnectorReferenceEntityByReferenceEntityIdentifierInterface $findConnectorReferenceEntity,
        AddHalDownloadLinkToReferenceEntityImage $addHalLinksToImageValues,
        SecurityFacade $securityFacade
    ) {
        $this->findConnectorReferenceEntity = $findConnectorReferenceEntity;
        $this->addHalLinksToReferenceEntityImage = $addHalLinksToImageValues;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $code): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

        try {
            $code = ReferenceEntityIdentifier::fromString($code);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $referenceEntity = $this->findConnectorReferenceEntity->find($code);

        if (!$referenceEntity instanceof ConnectorReferenceEntity) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $code));
        }

        $normalizedReferenceEntity = $referenceEntity->normalize();
        $normalizedReferenceEntity = ($this->addHalLinksToReferenceEntityImage)($normalizedReferenceEntity);

        return new JsonResponse($normalizedReferenceEntity);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list reference entities.');
        }
    }
}
