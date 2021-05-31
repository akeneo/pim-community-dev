<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAllAssetFamilyAssetsCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAllAssetFamilyAssetsHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteProductLinkRulesAction
{
    private LinkAllAssetFamilyAssetsHandler $linkAllAssetFamilyAssetsHandler;

    private TokenStorageInterface $tokenStorage;

    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;

    private SecurityFacade $securityFacade;

    public function __construct(
        LinkAllAssetFamilyAssetsHandler $linkAllAssetFamilyAssetsHandler,
        TokenStorageInterface $tokenStorage,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        SecurityFacade $securityFacade
    ) {
        $this->linkAllAssetFamilyAssetsHandler = $linkAllAssetFamilyAssetsHandler;
        $this->tokenStorage = $tokenStorage;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(string $identifier): JsonResponse
    {
        if (!$this->isUserAllowedToEdit($identifier)) {
            throw new AccessDeniedHttpException();
        }

        $command = new LinkAllAssetFamilyAssetsCommand($identifier);
        try {
            ($this->linkAllAssetFamilyAssetsHandler)($command);
        } catch (AssetFamilyNotFoundException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToEdit(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_asset_family_edit')
            && $this->securityFacade->isGranted('akeneo_assetmanager_asset_family_execute_product_link_rule')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }
}
