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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset;

use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class DeleteAssetAction
{
    private DeleteAssetHandler $deleteAssetHandler;
    private SecurityFacade $securityFacade;

    public function __construct(
        DeleteAssetHandler $deleteAssetHandler,
        SecurityFacade $securityFacade
    ) {
        $this->deleteAssetHandler = $deleteAssetHandler;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(string $assetFamilyIdentifier, string $code): Response
    {
        $this->denyAccessUnlessAclIsGranted();

        try {
            $assetCode = AssetCode::fromString($code);
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $command = new DeleteAssetCommand($assetCode->normalize(), $assetFamilyIdentifier->normalize());

        try {
            ($this->deleteAssetHandler)($command);
        } catch (AssetNotFoundException $exception) {
            return new JsonResponse([
                'code'    => Response::HTTP_NOT_FOUND,
                'message' => sprintf('Resource `%s` does not exist.', $assetCode->normalize()),
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_asset_remove')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to delete assets.');
        }
    }
}
