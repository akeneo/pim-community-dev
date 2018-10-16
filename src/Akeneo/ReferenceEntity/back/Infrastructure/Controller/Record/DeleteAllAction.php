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
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    public function __construct(
        DeleteAllReferenceEntityRecordsHandler $deleteAllRecordsHandler,
        SecurityFacade $securityFacade
    ) {
        $this->deleteAllRecordsHandler = $deleteAllRecordsHandler;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->securityFacade->isGranted('akeneo_referenceentity_records_delete_all')) {
            throw new AccessDeniedException();
        }

        $command = new DeleteAllReferenceEntityRecordsCommand();
        $command->referenceEntityIdentifier = $referenceEntityIdentifier;

        ($this->deleteAllRecordsHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
