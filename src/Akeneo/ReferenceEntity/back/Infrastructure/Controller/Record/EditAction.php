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

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
class EditAction
{
    public function __construct(
        private EditRecordCommandFactory $editRecordCommandFactory,
        private EditRecordHandler $editRecordHandler,
        private ValidatorInterface $validator,
        private CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        private TokenStorageInterface $tokenStorage,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToEdit($request->get('referenceEntityIdentifier'))) {
            throw new AccessDeniedException();
        }
        if ($this->hasDesynchronizedIdentifiers($request)) {
            return new JsonResponse(
                'The identifier provided in the route and the one given in the body of the request are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = $this->getEditCommand($request);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->editRecordHandler)($command);
        } catch (RecordNotFoundException) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToEdit(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery(
            $referenceEntityIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUserIdentifier()
        );

        return ($this->canEditReferenceEntityQueryHandler)($query);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifiers(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['reference_entity_identifier'] !== $request->get('referenceEntityIdentifier') ||
            $normalizedCommand['code'] !== $request->get('recordCode');
    }

    private function getEditCommand(Request $request): EditRecordCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $this->editRecordCommandFactory->create($normalizedCommand);
    }
}
