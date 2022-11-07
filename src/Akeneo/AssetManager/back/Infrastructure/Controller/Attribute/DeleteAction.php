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
use Akeneo\AssetManager\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\AssetManager\Domain\Exception\CantDeleteAttributeUsedAsLabelException;
use Akeneo\AssetManager\Domain\Exception\CantDeleteMainMediaException;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DeleteAction
{
    public function __construct(
        private DeleteAttributeHandler $deleteAttributeHandler,
        private SecurityFacadeInterface $securityFacade,
        private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier, string $attributeIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToDelete($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }

        $command = new DeleteAttributeCommand($attributeIdentifier);

        try {
            ($this->deleteAttributeHandler)($command);
        } catch (AttributeNotFoundException) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        } catch (CantDeleteMainMediaException|CantDeleteAttributeUsedAsLabelException) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToDelete(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUserIdentifier()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_attribute_delete')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }
}
