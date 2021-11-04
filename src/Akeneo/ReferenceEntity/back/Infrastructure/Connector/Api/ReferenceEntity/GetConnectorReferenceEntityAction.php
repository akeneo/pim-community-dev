<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\Hal\AddHalDownloadLinkToReferenceEntityImage;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetConnectorReferenceEntityAction
{
    private FindConnectorReferenceEntityByReferenceEntityIdentifierInterface $findConnectorReferenceEntity;

    private ReferenceEntityExistsInterface $referenceEntityExists;

    private AddHalDownloadLinkToReferenceEntityImage $addHalLinksToReferenceEntityImage;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    private LoggerInterface $apiAclLogger;

    public function __construct(
        FindConnectorReferenceEntityByReferenceEntityIdentifierInterface $findConnectorReferenceEntity,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AddHalDownloadLinkToReferenceEntityImage $addHalLinksToImageValues,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorReferenceEntity = $findConnectorReferenceEntity;
        $this->addHalLinksToReferenceEntityImage = $addHalLinksToImageValues;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
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
        $acl = 'pim_api_reference_entity_list';

        if (!$this->securityFacade->isGranted($acl)) {
            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                throw new \LogicException('An user must be authenticated if ACLs are required');
            }

            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                throw new \LogicException(sprintf(
                    'An instance of "%s" is expected if ACLs are required',
                    UserInterface::class
                ));
            }

            $this->apiAclLogger->warning(sprintf(
                'User "%s" with roles %s is not granted "%s"',
                $user->getUsername(),
                implode(',', $user->getRoles()),
                $acl
            ));
        }
    }
}
