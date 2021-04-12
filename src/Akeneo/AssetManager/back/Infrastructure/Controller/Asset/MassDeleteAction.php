<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\Asset;

use Akeneo\AssetManager\Application\Asset\MassDeleteAssets\MassDeleteAssetsCommand;
use Akeneo\AssetManager\Application\Asset\MassDeleteAssets\MassDeleteAssetsHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Delete assets for a given selection
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteAction
{
    private MassDeleteAssetsHandler $massDeleteAssetsHandler;
    private SecurityFacade $securityFacade;
    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        MassDeleteAssetsHandler $massDeleteAssetsHandler,
        SecurityFacade $securityFacade,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->massDeleteAssetsHandler = $massDeleteAssetsHandler;
        $this->securityFacade = $securityFacade;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->isUserAllowedToMassDeleteAssets($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }

        $normalizedQuery = json_decode($request->getContent(), true);
        $query = AssetQuery::createFromNormalized($normalizedQuery);
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($assetFamilyIdentifier);

        if ($this->hasDesynchronizedIdentifiers($assetFamilyIdentifier, $query)) {
            return new JsonResponse(
                'The asset family identifier provided in the route and the one given in the request body are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = new MassDeleteAssetsCommand((string) $assetFamilyIdentifier, $query->normalize());

        ($this->massDeleteAssetsHandler)($command);

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }

    private function isUserAllowedToMassDeleteAssets(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_asset_delete')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getAssetFamilyIdentifierOr404(string $identifier): AssetFamilyIdentifier
    {
        try {
            return AssetFamilyIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifiers(
        AssetFamilyIdentifier $routeAssetFamilyIdentifier,
        AssetQuery $query
    ): bool {
        return (string) $routeAssetFamilyIdentifier !== $query->getFilter('asset_family')['value'];
    }
}
