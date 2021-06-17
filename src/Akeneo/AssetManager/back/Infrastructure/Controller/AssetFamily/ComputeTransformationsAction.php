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

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetFamilyIdentifierLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ComputeTransformationsAction
{
    private FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery;

    private ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationsLauncher;

    public function __construct(
        FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery,
        ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationsLauncher
    ) {
        $this->findOneAssetFamilyQuery = $findOneAssetFamilyQuery;
        $this->computeTransformationsLauncher = $computeTransformationsLauncher;
    }

    public function __invoke(string $identifier): JsonResponse
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($identifier);
        $this->findAssetFamilyDetailsOr404($assetFamilyIdentifier);
        $this->computeTransformationsLauncher->launch($assetFamilyIdentifier);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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

        return $result;
    }
}
