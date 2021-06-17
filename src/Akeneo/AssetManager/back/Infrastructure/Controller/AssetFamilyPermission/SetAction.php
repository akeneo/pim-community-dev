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

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamilyPermission;

use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions\SetAssetFamilyPermissionsCommand;
use Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions\SetAssetFamilyPermissionsHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions\SetUserGroupPermissionCommand;
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
    private SetAssetFamilyPermissionsHandler $setAssetFamilyPermissionsHandler;

    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;

    private TokenStorageInterface $tokenStorage;

    private SecurityFacade $securityFacade;

    private ValidatorInterface $validator;

    private Serializer $serializer;

    public function __construct(
        SetAssetFamilyPermissionsHandler $setAssetFamilyPermissionsHandler,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage,
        SecurityFacade $securityFacade,
        ValidatorInterface $validator,
        Serializer $serializer
    ) {
        $this->setAssetFamilyPermissionsHandler = $setAssetFamilyPermissionsHandler;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->securityFacade = $securityFacade;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToEdit($assetFamilyIdentifier)) {
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

        $setPermissionsCommand = new SetAssetFamilyPermissionsCommand(
            $assetFamilyIdentifier,
            $userGroupPermissionCommands
        );

        $violations = $this->validator->validate($setPermissionsCommand);
        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->serializer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        ($this->setAssetFamilyPermissionsHandler)($setPermissionsCommand);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToEdit(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );
        $isAllowedToEdit = ($this->canEditAssetFamilyQueryHandler)($query);

        return $this->securityFacade->isGranted('akeneo_assetmanager_asset_family_manage_permission') && $isAllowedToEdit;
    }
}
