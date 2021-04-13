<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Record;

use Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords\MassDeleteRecordsCommand;
use Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords\MassDeleteRecordsHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Delete records for a given selection
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteAction
{
    private MassDeleteRecordsHandler $massDeleteRecordsHandler;
    private SecurityFacade $securityFacade;
    private CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        MassDeleteRecordsHandler $massDeleteRecordsHandler,
        SecurityFacade $securityFacade,
        CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->massDeleteRecordsHandler = $massDeleteRecordsHandler;
        $this->securityFacade = $securityFacade;
        $this->canEditReferenceEntityQueryHandler = $canEditReferenceEntityQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->isUserAllowedToMassDeleteRecords($request->get('referenceEntityIdentifier'))) {
            throw new AccessDeniedException();
        }

        $normalizedQuery = json_decode($request->getContent(), true);
        $query = RecordQuery::createFromNormalized($normalizedQuery);
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($referenceEntityIdentifier);

        if ($this->hasDesynchronizedIdentifiers($referenceEntityIdentifier, $query)) {
            return new JsonResponse(
                'The Reference entity identifier provided in the route and the one given in the request body are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = new MassDeleteRecordsCommand((string) $referenceEntityIdentifier, $query->normalize());

        ($this->massDeleteRecordsHandler)($command);

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }

    private function isUserAllowedToMassDeleteRecords(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery(
            $referenceEntityIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_referenceentity_record_delete')
            && ($this->canEditReferenceEntityQueryHandler)($query);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getReferenceEntityIdentifierOr404(string $identifier): ReferenceEntityIdentifier
    {
        try {
            return ReferenceEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifiers(
        ReferenceEntityIdentifier $routeReferenceEntityIdentifier,
        RecordQuery $query
    ): bool {
        return (string) $routeReferenceEntityIdentifier !== $query->getFilter('reference_entity')['value'];
    }
}
