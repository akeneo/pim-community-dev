<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteAssetFamilyNamingConventionCommand;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteAssetFamilyNamingConventionHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionAction
{
    private ExecuteAssetFamilyNamingConventionHandler $executeAssetFamilyNamingConventionHandler;

    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        ExecuteAssetFamilyNamingConventionHandler $executeAssetFamilyNamingConventionHandler,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage,
        SecurityFacade $securityFacade
    ) {
        $this->executeAssetFamilyNamingConventionHandler = $executeAssetFamilyNamingConventionHandler;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        if (!$this->isUserAllowed($assetFamilyIdentifier)) {
            throw new AccessDeniedHttpException();
        }

        $command = new ExecuteAssetFamilyNamingConventionCommand($assetFamilyIdentifier);
        try {
            ($this->executeAssetFamilyNamingConventionHandler)($command);
        } catch (AssetFamilyNotFoundException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function isUserAllowed(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_asset_family_edit')
            && $this->securityFacade->isGranted('akeneo_assetmanager_asset_family_execute_naming_conventions')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }
}
