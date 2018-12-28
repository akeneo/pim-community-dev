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

use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetReferenceEntityPermissionsCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetReferenceEntityPermissionsHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetUserGroupPermissionCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    /** @var ValidatorInterface */
    private $validator;

    /** @var Serializer */
    private $serializer;

    public function __construct(
        SetReferenceEntityPermissionsHandler $setReferenceEntityPermissionsHandler,
        ValidatorInterface $validator,
        Serializer $serializer
    ) {
        $this->setReferenceEntityPermissionsHandler = $setReferenceEntityPermissionsHandler;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier)
    {
        $permissions = json_decode($request->getContent(), true);
        $userGroupPermissionCommands = [];
        foreach ($permissions as $permission) {
            $command = new SetUserGroupPermissionCommand();
            $command->userGroupIdentifier = $permission['user_group_identifier'];
            $command->rightLevel = $permission['right_level'];

            $userGroupPermissionCommands[] = $command;
        }

        $setPermissionsCommand = new SetReferenceEntityPermissionsCommand();
        $setPermissionsCommand->referenceEntityIdentifier = $referenceEntityIdentifier;
        $setPermissionsCommand->permissionsByUserGroup = $userGroupPermissionCommands;

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
}
