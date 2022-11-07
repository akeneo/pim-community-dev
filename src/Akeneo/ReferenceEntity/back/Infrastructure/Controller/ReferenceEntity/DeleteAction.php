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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\ReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;
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
 * Delete an Reference Entity
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAction
{
    public function __construct(
        private SecurityFacade $securityFacade,
        private CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        private TokenStorageInterface $tokenStorage,
        private NormalizerInterface $normalizer,
        private ValidatorInterface $validator,
        private DeleteReferenceEntityHandler $deleteReferenceEntityHandler
    ) {
    }

    public function __invoke(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToDelete($request->get('identifier'))) {
            throw new AccessDeniedException();
        }

        $command = $this->getDeleteCommand($identifier);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            ($this->deleteReferenceEntityHandler)($command);
        } catch (ReferenceEntityNotFoundException) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToDelete(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery(
            $referenceEntityIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUserIdentifier()
        );

        return $this->securityFacade->isGranted('akeneo_referenceentity_reference_entity_delete')
            && ($this->canEditReferenceEntityQueryHandler)($query);
    }

    private function getDeleteCommand(string $identifier): DeleteReferenceEntityCommand
    {
        return new DeleteReferenceEntityCommand($identifier);
    }
}
