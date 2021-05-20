<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamilyPermission;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\FindAssetFamilyPermissionsDetailsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\PermissionDetails;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetAction
{
    private FindAssetFamilyPermissionsDetailsInterface $findAssetFamilyPermissionsDetails;

    public function __construct(FindAssetFamilyPermissionsDetailsInterface $findAssetFamilyPermissionsDetails)
    {
        $this->findAssetFamilyPermissionsDetails = $findAssetFamilyPermissionsDetails;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): JsonResponse
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($assetFamilyIdentifier);
        $assetFamilyPermissionDetails = $this->findAssetFamilyPermissionsDetails->find($assetFamilyIdentifier);
        $result = $this->normalizePermissionDetails($assetFamilyPermissionDetails);

        return new JsonResponse($result);
    }

    private function getAssetFamilyIdentifierOr404(string $identifier): AssetFamilyIdentifier
    {
        try {
            return AssetFamilyIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @param PermissionDetails[] $assetFamilyPermissionDetails
     */
    private function normalizePermissionDetails(array $assetFamilyPermissionDetails): array
    {
        return array_map(fn(PermissionDetails $permissionDetails) => $permissionDetails->normalize(), $assetFamilyPermissionDetails);
    }
}
