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

use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate & save a reference entity
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAction
{
    /** @var EditReferenceEntityHandler */
    private $editReferenceEntityHandler;

    /** @var Serializer */
    private $serializer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CanEditReferenceEntityQueryHandler */
    private $canEditReferenceEntityQueryHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        EditReferenceEntityHandler $editReferenceEntityHandler,
        CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        TokenStorageInterface $tokenStorage,
        Serializer $serializer,
        ValidatorInterface $validator
    ) {
        $this->editReferenceEntityHandler = $editReferenceEntityHandler;
        $this->canEditReferenceEntityQueryHandler = $canEditReferenceEntityQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if ($this->hasDesynchronizedIdentifier($request)) {
            return new JsonResponse(
                'Reference entity identifier provided in the route and the one given in the body of your request are different',
                Response::HTTP_BAD_REQUEST
            );
        }
        if (!$this->isUserAllowedToEdit($request->get('identifier'))) {
            throw new AccessDeniedHttpException();
        }

        $command = $this->serializer->deserialize($request->getContent(), EditReferenceEntityCommand::class, 'json');
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse($this->serializer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST);
        }

        ($this->editReferenceEntityHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifier(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['identifier'] !== $request->get('identifier');
    }

    private function isUserAllowedToEdit(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery();
        $query->securityIdentifier = $this->tokenStorage->getToken()->getUser()->getUsername();
        $query->referenceEntityIdentifier = $referenceEntityIdentifier;
        $isAllowedToEdit = ($this->canEditReferenceEntityQueryHandler)($query);

        return $isAllowedToEdit; // && add Check of ACLs
    }
}
