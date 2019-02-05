<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Record;

use Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords\DeleteAllReferenceEntityRecordsCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords\DeleteAllReferenceEntityRecordsHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Delete all records belonging to a reference entity
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAllAction
{
    /** @var DeleteAllReferenceEntityRecordsHandler */
    private $deleteAllRecordsHandler;

    /** @var SecurityFacade */
    private $securityFacade;
    /** @var CanEditReferenceEntityQueryHandler */
    private $canEditReferenceEntityQueryHandler;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        DeleteAllReferenceEntityRecordsHandler $deleteAllRecordsHandler,
        SecurityFacade $securityFacade,
        CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->deleteAllRecordsHandler = $deleteAllRecordsHandler;
        $this->securityFacade = $securityFacade;
        $this->canEditReferenceEntityQueryHandler = $canEditReferenceEntityQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToDeleteAllRecords($request->get('referenceEntityIdentifier'))) {
            throw new AccessDeniedException();
        }

        $command = new DeleteAllReferenceEntityRecordsCommand($referenceEntityIdentifier);

        ($this->deleteAllRecordsHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToDeleteAllRecords(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery(
            $referenceEntityIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_referenceentity_records_delete_all')
            && ($this->canEditReferenceEntityQueryHandler)($query);
    }
}
