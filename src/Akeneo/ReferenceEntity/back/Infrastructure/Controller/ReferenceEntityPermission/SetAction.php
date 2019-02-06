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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetReferenceEntityPermissionsCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetReferenceEntityPermissionsHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetUserGroupPermissionCommand;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SetAction
{
    /** @var SetReferenceEntityPermissionsHandler */
    private $setReferenceEntityPermissionsHandler;

    /** @var CanEditReferenceEntityQueryHandler */
    private $canEditReferenceEntityQueryHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var ValidatorInterface */
    private $validator;

    /** @var Serializer */
    private $serializer;

    public function __construct(
        SetReferenceEntityPermissionsHandler $setReferenceEntityPermissionsHandler,
        CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        TokenStorageInterface $tokenStorage,
        SecurityFacade $securityFacade,
        ValidatorInterface $validator,
        Serializer $serializer
    ) {
        $this->setReferenceEntityPermissionsHandler = $setReferenceEntityPermissionsHandler;
        $this->canEditReferenceEntityQueryHandler = $canEditReferenceEntityQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->securityFacade = $securityFacade;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToEdit($referenceEntityIdentifier)) {
            throw new AccessDeniedHttpException();
        }

        $permissions = json_decode($request->getContent(), true);
        $userGroupPermissionCommands = [];
        foreach ($permissions as $permission) {
            $command = new SetUserGroupPermissionCommand(
                $permission['user_group_identifier'],
                $permission['right_level']
            );

            $userGroupPermissionCommands[] = $command;
        }

        $setPermissionsCommand = new SetReferenceEntityPermissionsCommand(
            $referenceEntityIdentifier,
            $userGroupPermissionCommands
        );

        $violations = $this->validator->validate($setPermissionsCommand);
        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->serializer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        ($this->setReferenceEntityPermissionsHandler)($setPermissionsCommand);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToEdit(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery(
            $referenceEntityIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );
        $isAllowedToEdit = ($this->canEditReferenceEntityQueryHandler)($query);

        return $this->securityFacade->isGranted('akeneo_referenceentity_reference_entity_manage_permission') && $isAllowedToEdit;
    }
}
