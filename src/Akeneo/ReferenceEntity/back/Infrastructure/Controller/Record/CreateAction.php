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

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Domain\Exception\RecordAlreadyExistsError;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Normalizer\ErrorFacingFrontendNormalizer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate & save a record
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAction
{
    public function __construct(
        private CreateRecordHandler $createRecordHandler,
        private RecordIndexerInterface $recordIndexer,
        private CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        private TokenStorageInterface $tokenStorage,
        private NormalizerInterface $normalizer,
        private ValidatorInterface $validator,
        private SecurityFacade $securityFacade,
        private ErrorFacingFrontendNormalizer $errorFacingFrontendNormalizer,
    ) {
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToCreate($request->get('referenceEntityIdentifier'))) {
            throw new AccessDeniedException();
        }
        if ($this->hasDesynchronizedIdentifier($request)) {
            return new JsonResponse(
                'Reference Entity Identifier provided in the route and the one given in the body of your request are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = $this->getCreateCommand($request);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->createRecord($command);
        } catch (RecordAlreadyExistsError $error) {
            return new JsonResponse(
                $this->errorFacingFrontendNormalizer->normalize($error, 'code'),
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToCreate(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery(
            $referenceEntityIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUserIdentifier()
        );

        return $this->securityFacade->isGranted('akeneo_referenceentity_record_create')
            && ($this->canEditReferenceEntityQueryHandler)($query);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifier(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['reference_entity_identifier'] !== $request->get('referenceEntityIdentifier');
    }

    private function getCreateCommand(Request $request): CreateRecordCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return new CreateRecordCommand(
            $normalizedCommand['reference_entity_identifier'] ?? null,
            $normalizedCommand['code'] ?? null,
            $normalizedCommand['labels'] ?? []
        );
    }

    /**
     * When creating multiple records in a row using the UI "Create another",
     * we force refresh of the index so that the grid is up to date when the users dismisses the creation modal.
     *
     * @throws RecordAlreadyExistsError
     */
    private function createRecord(CreateRecordCommand $command): void
    {
        ($this->createRecordHandler)($command);
        $this->recordIndexer->refresh();
    }
}
