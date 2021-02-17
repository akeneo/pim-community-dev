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

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\EventAggregatorInterface as ComputeTransformationEventAggregatorInterface;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\Connector\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Connector\EditAssetCommandFactory as NamingConventionEditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionException;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\IndexAssetEventAggregator;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateOrUpdateAssetAction
{
    /** @var AssetFamilyExistsInterface */
    private $assetFamilyExists;

    /** @var AssetExistsInterface */
    private $assetExists;

    /** @var EditAssetCommandFactory */
    private $editAssetCommandFactory;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    /** @var CreateAssetHandler */
    private $createAssetHandler;

    /** @var Router */
    private $router;

    /** @var AssetValidator */
    private $assetStructureValidator;

    /** @var ValidatorInterface */
    private $assetDataValidator;

    /** @var BatchAssetsToLink */
    private $batchAssetsToLink;

    /** @var NamingConventionEditAssetCommandFactory */
    private $namingConventionEditAssetCommandFactory;

    /** @var IndexAssetEventAggregator */
    private $indexAssetEventAggregator;

    /** @var ComputeTransformationEventAggregatorInterface */
    private $computeTransformationEventAggregator;

    public function __construct(
        AssetFamilyExistsInterface $assetFamilyExists,
        AssetExistsInterface $assetExists,
        EditAssetCommandFactory $editAssetCommandFactory,
        EditAssetHandler $editAssetHandler,
        CreateAssetHandler $createAssetHandler,
        Router $router,
        AssetValidator $assetStructureValidator,
        ValidatorInterface $assetDataValidator,
        BatchAssetsToLink $batchAssetsToLink,
        NamingConventionEditAssetCommandFactory $namingConventionEditAssetCommandFactory,
        EventAggregatorInterface $indexAssetEventAggregator,
        ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->assetExists = $assetExists;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->editAssetHandler = $editAssetHandler;
        $this->createAssetHandler = $createAssetHandler;
        $this->router = $router;
        $this->assetStructureValidator = $assetStructureValidator;
        $this->assetDataValidator = $assetDataValidator;
        $this->batchAssetsToLink = $batchAssetsToLink;
        $this->namingConventionEditAssetCommandFactory = $namingConventionEditAssetCommandFactory;
        $this->indexAssetEventAggregator = $indexAssetEventAggregator;
        $this->computeTransformationEventAggregator = $computeTransformationEventAggregator;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier, string $code): Response
    {
        try {
            $assetCode = AssetCode::fromString($code);
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (!$this->assetFamilyExists->withIdentifier($assetFamilyIdentifier)) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $assetFamilyIdentifier));
        }

        $normalizedAsset = $this->getNormalizedAssetFromRequest($request);
        $structureErrors = $this->assetStructureValidator->validate($assetFamilyIdentifier, $normalizedAsset);

        if (!empty($structureErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The asset has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($structureErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $createAssetCommand = $this->createValidatedAssetCommandIfNeeded($assetFamilyIdentifier, $assetCode);
        $editAssetCommand = $this->createValidatedEditCommand($assetFamilyIdentifier, $normalizedAsset);
        $namingConventionEditCommand = $this->createValidatedNamingConventionCommandIfNeeded(
            $assetFamilyIdentifier,
            $normalizedAsset
        );

        $responseStatusCode = Response::HTTP_NO_CONTENT;

        if (null !== $createAssetCommand) {
            $responseStatusCode = Response::HTTP_CREATED;
            ($this->createAssetHandler)($createAssetCommand);
            if (null !== $namingConventionEditCommand) {
                $editAssetCommand->editAssetValueCommands = array_merge($editAssetCommand->editAssetValueCommands, $namingConventionEditCommand->editAssetValueCommands);
            }

            ($this->editAssetHandler)($editAssetCommand);

            $this->batchAssetsToLink->add($createAssetCommand->assetFamilyIdentifier, $createAssetCommand->code);
        } else {
            ($this->editAssetHandler)($editAssetCommand);
        }

        $this->indexAssetEventAggregator->flushEvents();
        $this->computeTransformationEventAggregator->flushEvents();

        return $this->createResponse($responseStatusCode, $assetFamilyIdentifier, $assetCode);
    }

    private function createValidatedAssetCommandIfNeeded(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): ?CreateAssetCommand
    {
        if ($this->assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)) {
            return null;
        }

        $command = new CreateAssetCommand(
            $assetFamilyIdentifier->normalize(),
            $assetCode->normalize(),
            []
        );

        $violations = $this->assetDataValidator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The asset has data that does not comply with the business rules.');
        }

        return $command;
    }

    private function createValidatedNamingConventionCommandIfNeeded(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $normalizedAsset
    ): ?EditAssetCommand {
        try {
            $editAssetCommand = $this->namingConventionEditAssetCommandFactory->create(
                $normalizedAsset,
                $assetFamilyIdentifier
            );
        } catch (NamingConventionException $e) {
            if ($e->namingConventionAbortOnError()) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Error during naming convention execution: %s', $e->getMessage())
                );
            }

            // The naming convention execution can not be executed but we continue.
            $namingConventionEditCommand = null;
            // @TODO AST-205: How do we display the warning message to the end user?

            return null;
        }

        $violations = $this->assetDataValidator->validate($editAssetCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The asset data computed with naming convention do not comply with the business rules.');
        }

        return $editAssetCommand;
    }

    private function createValidatedEditCommand(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAsset): EditAssetCommand
    {
        try {
            $editAssetCommand = $this->editAssetCommandFactory->create($assetFamilyIdentifier, $normalizedAsset);
        } catch (\InvalidArgumentException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $violations = $this->assetDataValidator->validate($editAssetCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The asset has data that does not comply with the business rules.');
        }

        return $editAssetCommand;
    }

    private function getNormalizedAssetFromRequest(Request $request): array
    {
        $normalizedAsset = json_decode($request->getContent(), true);

        if (null === $normalizedAsset) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        $code = $request->get('code');
        $codeInBody = $normalizedAsset['code'] ?? null;
        if ($codeInBody !== $code) {
            throw new UnprocessableEntityHttpException('The code of the asset provided in the URI must be the same as the one provided in the request body.');
        }

        return $normalizedAsset;
    }

    private function createResponse(int $responseStatusCode, AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): Response
    {
        $headers = [
            'location' => $this->router->generate('akeneo_asset_manager_asset_rest_connector_get', [
                'assetFamilyIdentifier' => $assetFamilyIdentifier->normalize(),
                'code' => $assetCode->normalize(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', $responseStatusCode, $headers);
    }
}
