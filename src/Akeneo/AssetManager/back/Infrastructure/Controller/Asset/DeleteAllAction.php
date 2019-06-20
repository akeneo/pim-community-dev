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

use Akeneo\AssetManager\Application\Asset\DeleteAllAssets\DeleteAllAssetFamilyAssetsCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAllAssets\DeleteAllAssetFamilyAssetsHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Delete all assets belonging to an asset family
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAllAction
{
    /** @var DeleteAllAssetFamilyAssetsHandler */
    private $deleteAllAssetsHandler;

    /** @var SecurityFacade */
    private $securityFacade;
    /** @var CanEditAssetFamilyQueryHandler */
    private $canEditAssetFamilyQueryHandler;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        DeleteAllAssetFamilyAssetsHandler $deleteAllAssetsHandler,
        SecurityFacade $securityFacade,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->deleteAllAssetsHandler = $deleteAllAssetsHandler;
        $this->securityFacade = $securityFacade;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToDeleteAllAssets($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }

        $command = new DeleteAllAssetFamilyAssetsCommand($assetFamilyIdentifier);

        ($this->deleteAllAssetsHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToDeleteAllAssets(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_assets_delete_all')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }
}
