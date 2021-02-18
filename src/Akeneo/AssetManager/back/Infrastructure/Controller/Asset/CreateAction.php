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
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\EditAssetCommandFactory as NamingConventionEditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionException;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\IndexAssetEventAggregator;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate & save a asset
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAction
{
    /** @var CreateAssetHandler */
    private $createAssetHandler;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    /** @var AssetIndexerInterface */
    private $assetIndexer;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var CanEditAssetFamilyQueryHandler */
    private $canEditAssetFamilyQueryHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EditAssetCommandFactory */
    private $editAssetCommandFactory;

    /** @var NamingConventionEditAssetCommandFactory */
    private $namingConventionEditAssetCommandFactory;

    /** @var LinkAssetHandler */
    private $linkAssetHandler;

    /** @var IndexAssetEventAggregator */
    private $indexAssetEventAggregator;

    /** @var ComputeTransformationEventAggregatorInterface */
    private $computeTransformationEventAggregator;

    public function __construct(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        AssetIndexerInterface $assetIndexer,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade,
        EditAssetCommandFactory $editAssetCommandFactory,
        NamingConventionEditAssetCommandFactory $namingConventionEditAssetCommandFactory,
        LinkAssetHandler $linkAssetHandler,
        EventAggregatorInterface $indexAssetEventAggregator,
        ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator
    ) {
        $this->createAssetHandler = $createAssetHandler;
        $this->editAssetHandler = $editAssetHandler;
        $this->assetIndexer = $assetIndexer;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->namingConventionEditAssetCommandFactory = $namingConventionEditAssetCommandFactory;
        $this->linkAssetHandler = $linkAssetHandler;
        $this->indexAssetEventAggregator = $indexAssetEventAggregator;
        $this->computeTransformationEventAggregator = $computeTransformationEventAggregator;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToCreate($request->get('assetFamilyIdentifier'))) {
            throw new AccessDeniedException();
        }
        if ($this->hasDesynchronizedIdentifier($request)) {
            return new JsonResponse(
                'Asset Family Identifier provided in the route and the one given in the body of your request are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $createCommand = $this->getCreateCommand($request);
        $creationViolations = $this->validator->validate($createCommand);

        if ($creationViolations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($creationViolations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $editCommand = $this->getEditCommand($request);
        $editionViolations = $this->validator->validate($editCommand);

        if ($editionViolations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($editionViolations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        // This solution is clearly not perfect but we don't find a better way to
        //  - validate the command and ensure the execution of naming convention will work safely
        //  - do not create the asset if the naming convention fails in strict mode
        // The entire create workflow should be rework in order to avoid too much business logic in controller (AST-259)
        try {
            $namingConventionEditCommand = $this->getNamingConventionEditCommand($request);
        } catch (NamingConventionException $e) {
            if ($e->namingConventionAbortOnError()) {

                // TODO AST-410
                // FIXME: This is a dirty fix to display the error messages in the UI, find a better solution
                // Also, error message should be translated
                $message = sprintf('Could not execute naming convention: %s', $e->getMessage());
                $violations = new ConstraintViolationList([
                    new ConstraintViolation($message, $message, [], null, 'code', null)
                ]);
                return new JsonResponse(
                    $this->normalizer->normalize($violations, 'internal_api'),
                    Response::HTTP_BAD_REQUEST
                );
            }

            // The naming convention execution can not be executed but we continue.
            $namingConventionEditCommand = null;
        }
        $namingConventionEditViolations = $this->validator->validate($namingConventionEditCommand);
        if ($namingConventionEditViolations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($namingConventionEditViolations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        ($this->createAssetHandler)($createCommand);
        if (null !== $namingConventionEditCommand) {
            $editCommand->editAssetValueCommands = array_merge($editCommand->editAssetValueCommands, $namingConventionEditCommand->editAssetValueCommands);
        }
        ($this->editAssetHandler)($editCommand);
        $this->linkAsset($request);

        $this->indexAssetEventAggregator->flushEvents();
        $this->computeTransformationEventAggregator->flushEvents();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToCreate(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );

        return $this->securityFacade->isGranted('akeneo_assetmanager_asset_create')
            && ($this->canEditAssetFamilyQueryHandler)($query);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifier(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['asset_family_identifier'] !== $request->get('assetFamilyIdentifier');
    }

    private function getCreateCommand(Request $request): CreateAssetCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        $command = new CreateAssetCommand(
            $normalizedCommand['asset_family_identifier'] ?? null,
            $normalizedCommand['code'] ?? null,
            $normalizedCommand['labels'] ?? []
        );

        return $command;
    }

    private function getEditCommand(Request $request): EditAssetCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);
        $command = $this->editAssetCommandFactory->create($normalizedCommand);

        return $command;
    }

    private function getNamingConventionEditCommand(Request $request): EditAssetCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $this->namingConventionEditAssetCommandFactory->create($normalizedCommand);
    }

    private function linkAsset(Request $request): void
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        $command = new LinkAssetCommand();
        $command->assetFamilyIdentifier = $normalizedCommand['asset_family_identifier'];
        $command->assetCode = $normalizedCommand['code'];

        ($this->linkAssetHandler)($command);
    }
}
