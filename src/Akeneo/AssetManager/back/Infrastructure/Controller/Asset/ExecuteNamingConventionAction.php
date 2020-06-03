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
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionAction
{
    /** @var EditAssetHandler */
    private $editAssetHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CanEditAssetFamilyQueryHandler */
    private $canEditAssetFamilyQueryHandler;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var EditAssetCommandFactory */
    private $editAssetCommandFactory;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        EditAssetHandler $editAssetHandler,
        EditAssetCommandFactory $editAssetCommandFactory,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        SecurityFacade $securityFacade
    ) {
        $this->editAssetHandler = $editAssetHandler;
        $this->tokenStorage = $tokenStorage;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(string $assetFamilyIdentifier, string $assetCode): JsonResponse
    {
        if (!$this->isUserAllowedToEdit($assetFamilyIdentifier)) {
            throw new AccessDeniedHttpException();
        }

        $editAssetCommand = $this->editAssetCommandFactory->create(
            ['asset_family_identifier' => $assetFamilyIdentifier, 'code' => $assetCode]
        );
        $violations = $this->validator->validate($editAssetCommand);
        if ($violations->count() > 0) {
            return new JsonResponse('FIX ME', Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->editAssetHandler)($editAssetCommand);
        } catch (AssetFamilyNotFoundException|AssetNotFoundException $e) {
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
