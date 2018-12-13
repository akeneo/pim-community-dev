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

use Akeneo\ReferenceEntity\Application\Permission\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\Permission\PermissionCheckQueryHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Creates a reference entity
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAction
{
    /** @var CreateReferenceEntityHandler */
    private $createReferenceEntityHandler;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var PermissionCheckQueryHandler $permissionCheckQueryHandler */
    private $permissionCheckQueryHandler;

    /** @var UserContext */
    private $userContext;

    public function __construct(
        CreateReferenceEntityHandler $createReferenceEntityHandler,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade,
        PermissionCheckQueryHandler $permissionCheckQueryHandler,
        UserContext $userContext
    ) {
        $this->createReferenceEntityHandler = $createReferenceEntityHandler;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
        $this->permissionCheckQueryHandler = $permissionCheckQueryHandler;
        $this->userContext = $userContext;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->isAllowed($request)) {
            throw new AccessDeniedException();
        }

        $command = $this->getCreateCommand($request);
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            return new JsonResponse($this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST);
        }

        ($this->createReferenceEntityHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getCreateCommand(Request $request): CreateReferenceEntityCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        $command = new CreateReferenceEntityCommand();
        $command->code = $normalizedCommand['code'] ?? null;
        $command->labels = $normalizedCommand['labels'] ?? [];

        return $command;
    }

    protected function isAllowed(Request $request): bool
    {
        return $this->securityFacade->isGranted('akeneo_referenceentity_reference_entity_create')
            && $this->userHasPermission($request);
    }

    protected function userHasPermission(Request $request): bool
    {
        $user = $this->userContext->getUser();
        $referenceEntityCode = json_decode($request->getContent(), true)['code'] ?? null;
        if (null === $user || null === $referenceEntityCode) {
            return false;
        }

        $permissionCheckQuery = new CanEditReferenceEntityQuery();
        $permissionCheckQuery->userIdentifier = $user->getId();
        $permissionCheckQuery->referenceEntityIdentifier = $referenceEntityCode;

        return $this->permissionCheckQueryHandler->isAllowed($permissionCheckQuery);
    }
}
