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

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\EventAggregatorInterface as ComputeTransformationEventAggregatorInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate & save a asset
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAction
{
    public function __construct(
        private EditAssetCommandFactory $editAssetCommandFactory,
        private EditAssetHandler $editAssetHandler,
        private ValidatorInterface $validator,
        private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        private TokenStorageInterface $tokenStorage,
        private NormalizerInterface $normalizer,
        private EventAggregatorInterface $indexAssetEventAggregator,
        private ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToEdit($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }
        if ($this->hasDesynchronizedIdentifiers($request)) {
            return new JsonResponse(
                'The identifier provided in the route and the one given in the body of the request are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = $this->getEditCommand($request);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->editAssetHandler)($command);
        } catch (AssetNotFoundException) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->indexAssetEventAggregator->flushEvents();
        $this->computeTransformationEventAggregator->flushEvents();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToEdit(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUserIdentifier()
        );

        return ($this->canEditAssetFamilyQueryHandler)($query);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifiers(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['asset_family_identifier'] !== $request->get('assetFamilyIdentifier') ||
            $normalizedCommand['code'] !== $request->get('assetCode');
    }

    private function getEditCommand(Request $request): EditAssetCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $this->editAssetCommandFactory->create($normalizedCommand);
    }
}
