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

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Get one Asset family by its identifier
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class GetAction
{
    private FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery;

    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->findOneAssetFamilyQuery = $findOneAssetFamilyQuery;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(string $identifier): JsonResponse
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($identifier);
        $assetFamilyDetails = $this->findAssetFamilyDetailsOr404($assetFamilyIdentifier);

        return new JsonResponse($assetFamilyDetails->normalize());
    }

    private function getAssetFamilyIdentifierOr404(string $identifier): AssetFamilyIdentifier
    {
        try {
            return AssetFamilyIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    private function findAssetFamilyDetailsOr404(AssetFamilyIdentifier $identifier): AssetFamilyDetails
    {
        $result = $this->findOneAssetFamilyQuery->find($identifier);
        if (null === $result) {
            throw new NotFoundHttpException();
        }

        return $this->hydratePermissions($result);
    }

    private function hydratePermissions(AssetFamilyDetails $assetFamilyDetails): AssetFamilyDetails
    {
        $canEditQuery = new CanEditAssetFamilyQuery(
            (string) $assetFamilyDetails->identifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );
        $assetFamilyDetails->isAllowedToEdit = ($this->canEditAssetFamilyQueryHandler)($canEditQuery);

        return $assetFamilyDetails;
    }
}
