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

namespace Akeneo\AssetManager\Infrastructure\Controller\Asset;

use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Delete a asset
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAction
{
    private DeleteAssetHandler $deleteAssetHandler;

    private SecurityFacade $securityFacade;

    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        DeleteAssetHandler $deleteAssetHandler,
        SecurityFacade $securityFacade,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->deleteAssetHandler = $deleteAssetHandler;
        $this->securityFacade = $securityFacade;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier, string $assetCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToDelete($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }

        $command = new DeleteAssetCommand($assetCode, $assetFamilyIdentifier);

        try {
            ($this->deleteAssetHandler)($command);
        } catch (AssetNotFoundException $exception) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToDelete(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_asset_delete')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }
}
