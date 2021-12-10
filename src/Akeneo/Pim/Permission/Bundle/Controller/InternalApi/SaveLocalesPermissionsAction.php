<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Saver\UserGroupLocalePermissionsSaver;
use Akeneo\Pim\Permission\Component\Validator\UpdateUserGroupLocalesPermissions;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class SaveLocalesPermissionsAction
{
    private SecurityFacade $securityFacade;
    private ValidatorInterface $validator;
    private UserGroupLocalePermissionsSaver $permissionsSaver;

    public function __construct(
        SecurityFacade $securityFacade,
        ValidatorInterface $validator,
        UserGroupLocalePermissionsSaver $permissionsSaver
    ) {
        $this->securityFacade = $securityFacade;
        $this->validator = $validator;
        $this->permissionsSaver = $permissionsSaver;
    }

    public function __invoke(
        Request $request
    ): Response {
        if (false === $request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (false === $this->securityFacade->isGranted('pimee_enrich_locale_edit_permissions')) {
            throw new AccessDeniedHttpException();
        }

        $payload = json_decode($request->getContent(), true);
        $validation = $this->validator->validate($payload, new UpdateUserGroupLocalesPermissions());

        if (0 < $validation->count()) {
            throw new BadRequestHttpException();
        }

        $this->permissionsSaver->save($payload['user_group'], $payload['permissions']);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
