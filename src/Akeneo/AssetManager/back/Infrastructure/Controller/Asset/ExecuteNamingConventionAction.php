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

namespace Akeneo\AssetManager\Infrastructure\Controller\Asset;

use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionAction
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private EditAssetHandler $editAssetHandler,
        private EditAssetCommandFactory $editAssetCommandFactory,
        private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function __invoke(string $assetFamilyIdentifier, string $assetCode): JsonResponse
    {
        if (!$this->isUserAllowedToEdit($assetFamilyIdentifier)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $asset = $this->assetRepository->getByAssetFamilyAndCode(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AssetCode::fromString($assetCode)
            );
        } catch (\Exception) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $normalizedAsset = $asset->normalize();
        $editAssetCommand = $this->editAssetCommandFactory->create(
            [
                'asset_family_identifier' => $assetFamilyIdentifier,
                'code' => $assetCode,
                'values' => $normalizedAsset['values'],
            ]
        );
        $violations = $this->validator->validate($editAssetCommand);
        if ($violations->count() > 0) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->editAssetHandler)($editAssetCommand);
        } catch (AssetFamilyNotFoundException | AssetNotFoundException) {
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
            && $this->securityFacade->isGranted('akeneo_assetmanager_asset_family_execute_naming_conventions')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }
}
