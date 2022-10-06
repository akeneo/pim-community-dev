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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Attribute;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
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
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAction
{
    public function __construct(
        private CreateAttributeHandler $createAttributeHandler,
        private CreateAttributeCommandFactoryRegistryInterface $attributeCommandFactoryRegistry,
        private CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        private TokenStorageInterface $tokenStorage,
        private NormalizerInterface $normalizer,
        private ValidatorInterface $validator,
        private SecurityFacade $securityFacade
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
        if (!$this->isAttributeTypeProvided($request)) {
            return new JsonResponse(
                'There was no valid attribute type provided in the request',
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

        ($this->createAttributeHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToCreate(string $referenceEntityIdentifier): bool
    {
        $query = new CanEditReferenceEntityQuery(
            $referenceEntityIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUserIdentifier()
        );

        return $this->securityFacade->isGranted('akeneo_referenceentity_attribute_create')
            && ($this->canEditReferenceEntityQueryHandler)($query);
    }

    private function getCreateCommand(Request $request): AbstractCreateAttributeCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $this->attributeCommandFactoryRegistry->getFactory($normalizedCommand)->create($normalizedCommand);
    }

    private function isAttributeTypeProvided($request)
    {
        $content = json_decode($request->getContent(), true);

        return isset($content['type']) && is_string($content['type']);
    }
}
