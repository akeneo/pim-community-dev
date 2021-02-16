<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\Asset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * - MassEdit create from normalized with edit actions
 * - MassEdit validator calling other
 */


/**
 * $updaters.each((updater) => {
 *  return { //Updater command
 *   type: updater['type'],
 *   command: $this->factory->createEditCommand($updater['data']);
 *  }
 * })
 */


/**
 * Edit assets for a given selection
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class ValidateMassEditAction
{
    const MASS_ACTION_TYPE = 'edit';

    private EditAssetCommandFactory $editAssetCommandFactory;
    private SecurityFacade $securityFacade;
    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;
    private TokenStorageInterface $tokenStorage;
    private ValidatorInterface $validator;
    private NormalizerInterface $normalizer;

    public function __construct(
        EditAssetCommandFactory $editAssetCommandFactory,
        SecurityFacade $securityFacade,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator,
        NormalizerInterface $normalizer
    ) {
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->securityFacade = $securityFacade;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->normalizer = $normalizer;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->isUserAllowedToMassEditAssets($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }

        $normalizedCommand = json_decode($request->getContent(), true);
        $query = AssetQuery::createFromNormalized($normalizedCommand['query']);
        $type = $normalizedCommand['type'];
        $normalizedUpdaters = $normalizedCommand['updaters'];
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($assetFamilyIdentifier);

        if ($this->hasDesynchronizedIdentifiers($assetFamilyIdentifier, $query)) {
            return new JsonResponse(
                'The asset family identifier provided in the route and the one given in the request body are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        if (self::MASS_ACTION_TYPE !== $type) {
            return new JsonResponse(
                'Only edit action type are supported',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = $this->createCommand((string) $assetFamilyIdentifier, $query->normalize(), $normalizedUpdaters);

        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }

    private function isUserAllowedToMassEditAssets(string $assetFamilyIdentifier): bool
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

    private function createCommand(string $assetFamilyIdentifier, array $query, array $normalizedUpdaters): MassEditAssetsCommand
    {
        $updaters = array_map(function ($updater) use ($assetFamilyIdentifier) {
            $fakeEditAssetCommand = $this->editAssetCommandFactory->create([
                'asset_family_identifier' => $assetFamilyIdentifier,
                'code' => 'FAKE_CODE_FOR_MASS_EDIT_VALIDATION_' . microtime(),
                'values' => [
                    [
                        'attribute' => $updater['attribute'],
                        'channel' => $updater['channel'],
                        'locale' => $updater['locale'],
                        'data' => $updater['data'],
                    ]
                ]
            ]);

            return [
                'action' => $updater['action'],
                'id' => $updater['id'],
                'command' => $fakeEditAssetCommand->editAssetValueCommands[0]
            ];
        }, $normalizedUpdaters);

        return new MassEditAssetsCommand($assetFamilyIdentifier, $query, $updaters);
    }
}
