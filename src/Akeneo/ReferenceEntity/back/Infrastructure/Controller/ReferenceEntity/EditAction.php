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

use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\ReferenceEntity\Application\Permission\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\Permission\PermissionQueryHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

    /** @var PermissionQueryHandler */
    private $permissionCheckQueryHandler;

    /** @var UserContext */
    private $userContext;

    public function __construct(
        EditReferenceEntityHandler $editReferenceEntityHandler,
        PermissionQueryHandler $permissionCheckQueryHandler,
        UserContext $userContext,
        Serializer $serializer,
        ValidatorInterface $validator
    ) {
        $this->editReferenceEntityHandler = $editReferenceEntityHandler;
        $this->permissionCheckQueryHandler = $permissionCheckQueryHandler;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->userContext = $userContext;
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
        if (!$this->isUserAllowedToEdit($request)) {
            throw new AccessDeniedHttpException();
        }

        $command = $this->serializer->deserialize($request->getContent(), EditReferenceEntityCommand::class, 'json');
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse($this->serializer->normalize($violations, 'internal_api'), Response::HTTP_BAD_REQUEST);
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

    private function isUserAllowedToEdit(Request $request): bool
    {
        return
            // $this->securityFacade->isGranted('akeneo_referenceentity_reference_entity_create') && // Check ACL?
            $this->userHasPermission($request);
    }

    private function userHasPermission(Request $request): bool
    {
        $user = $this->userContext->getUser();
        $referenceEntityCode = json_decode($request->getContent(), true)['code'] ?? null;
        if (null === $user || null === $referenceEntityCode) {
            return false;
        }

        $permissionCheckQuery = new CanEditReferenceEntityQuery();
        $permissionCheckQuery->userIdentifier = $user->getId();
        $permissionCheckQuery->referenceEntityIdentifier = $referenceEntityCode;

        return ($this->permissionCheckQueryHandler)($permissionCheckQuery);
    }
}
