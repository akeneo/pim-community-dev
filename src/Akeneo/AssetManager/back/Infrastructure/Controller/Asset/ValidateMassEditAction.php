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

use Akeneo\AssetManager\Application\Asset\MassEditAssets\CommandFactory\MassEditAssetsCommandFactory;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Edit assets for a given selection
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class ValidateMassEditAction
{
    private const MASS_ACTION_TYPE = 'edit';

    private SecurityFacade $securityFacade;
    private CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler;
    private TokenStorageInterface $tokenStorage;
    private ValidatorInterface $validator;
    private NormalizerInterface $normalizer;
    private MassEditAssetsCommandFactory $massEditAssetsCommandFactory;

    public function __construct(
        MassEditAssetsCommandFactory $massEditAssetsCommandFactory,
        SecurityFacade $securityFacade,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator,
        NormalizerInterface $normalizer
    ) {
        $this->securityFacade = $securityFacade;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->massEditAssetsCommandFactory = $massEditAssetsCommandFactory;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->isUserAllowedToMassEditAssets($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }

        $query = AssetQuery::createFromNormalized($request->request->get('query'));
        $type = $request->request->get('type');
        $normalizedUpdaters = $this->getUpdatersOr400($request);
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($assetFamilyIdentifier);

        if ($this->hasDesynchronizedIdentifiers($assetFamilyIdentifier, $query)) {
            return new JsonResponse(
                'The asset family identifier provided in the route and the one given in the request body are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        if (self::MASS_ACTION_TYPE !== $type) {
            return new JsonResponse(
                'Only edit action type is supported',
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $command = $this->massEditAssetsCommandFactory->create(
                $assetFamilyIdentifier,
                $query,
                $normalizedUpdaters
            );
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

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

    private function getUpdatersOr400(Request $request): array
    {
        $updaters = $request->request->get('updaters');

        if (!is_array($updaters)) {
            throw new BadRequestHttpException('Updaters should be an array');
        }

        return $updaters;
    }
}
