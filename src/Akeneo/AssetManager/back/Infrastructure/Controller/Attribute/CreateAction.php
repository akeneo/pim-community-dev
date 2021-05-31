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

namespace Akeneo\AssetManager\Infrastructure\Controller\Attribute;

use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributeNextOrderInterface;
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
    private CreateAttributeHandler $createAttributeHandler;

    private FindAttributeNextOrderInterface $attributeNextOrder;

    private SecurityFacade $securityFacade;

    private NormalizerInterface $normalizer;

    private ValidatorInterface $validator;

    private CreateAttributeCommandFactoryRegistryInterface $attributeCommandFactoryRegistry;

    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        CreateAttributeHandler $createAttributeHandler,
        FindAttributeNextOrderInterface $attributeNextOrder,
        CreateAttributeCommandFactoryRegistryInterface $attributeCommandFactoryRegistry,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->createAttributeHandler = $createAttributeHandler;
        $this->attributeNextOrder = $attributeNextOrder;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
        $this->attributeCommandFactoryRegistry = $attributeCommandFactoryRegistry;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToCreate($request->get('assetFamilyIdentifier'))) {
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

    private function isUserAllowedToCreate(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_attribute_create')
            && ($this->canEditAssetFamilyQueryHandler)($query);
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
