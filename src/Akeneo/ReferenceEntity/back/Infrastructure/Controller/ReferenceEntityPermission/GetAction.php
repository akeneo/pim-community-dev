<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\FindReferenceEntityPermissionsDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\PermissionDetails;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetAction
{
    /** @var FindReferenceEntityPermissionsDetailsInterface */
    private $findReferenceEntityPermissionsDetails;

    public function __construct(FindReferenceEntityPermissionsDetailsInterface $findReferenceEntityPermissionsDetails)
    {
        $this->findReferenceEntityPermissionsDetails = $findReferenceEntityPermissionsDetails;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): JsonResponse
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($referenceEntityIdentifier);
        $referenceEntityPermissionDetails = ($this->findReferenceEntityPermissionsDetails)($referenceEntityIdentifier);

        return new JsonResponse($this->normalizePermissionDetails($referenceEntityPermissionDetails));
    }

    private function getReferenceEntityIdentifierOr404(string $identifier): ReferenceEntityIdentifier
    {
        try {
            return ReferenceEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    private function normalizePermissionDetails($referenceEntityPermissionDetails): array
    {
        return array_map(function (PermissionDetails $permissionDetails) {
            return $permissionDetails->normalize();
        }, $referenceEntityPermissionDetails);
    }
}
