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

use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Asset get action.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class GetAction
{
    private FindAssetDetailsInterface $findAssetDetailsQuery;

    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        FindAssetDetailsInterface $findAssetDetailsQuery,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->findAssetDetailsQuery = $findAssetDetailsQuery;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(string $assetFamilyIdentifier, string $assetCode): JsonResponse
    {
        $assetCode = $this->getAssetCodeOr404($assetCode);
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($assetFamilyIdentifier);
        $assetDetails = $this->findAssetDetailsOr404($assetFamilyIdentifier, $assetCode);

        return new JsonResponse($assetDetails->normalize());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getAssetCodeOr404(string $assetCode): AssetCode
    {
        try {
            return AssetCode::fromString($assetCode);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getAssetFamilyIdentifierOr404(string $assetFamilyIdentifier): AssetFamilyIdentifier
    {
        try {
            return AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function findAssetDetailsOr404(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $assetCode
    ): AssetDetails {
        $result = $this->findAssetDetailsQuery->find($assetFamilyIdentifier, $assetCode);

        if (null === $result) {
            throw new NotFoundHttpException();
        }

        return $this->hydratePermissions($result);
    }

    private function hydratePermissions(AssetDetails $assetDetails): AssetDetails
    {
        $canEditQuery = new CanEditAssetFamilyQuery(
            (string) $assetDetails->assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );
        $assetDetails->isAllowedToEdit = ($this->canEditAssetFamilyQueryHandler)($canEditQuery);

        return $assetDetails;
    }
}
