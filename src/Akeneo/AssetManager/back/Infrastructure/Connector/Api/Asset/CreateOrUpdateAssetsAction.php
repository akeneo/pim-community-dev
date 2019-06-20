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

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\Connector\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetListValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateOrUpdateAssetsAction
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

    /** @var ValidatorInterface */
    private $assetDataValidator;

    /** @var ViolationNormalizer */
    private $violationNormalizer;

    /** @var AssetValidator */
    private $assetStructureValidator;

    /** @var AssetListValidator */
    private $assetListValidator;

    /** @var int */
    private $maximumAssetsPerRequest;

    public function __construct(
        AssetFamilyExistsInterface $assetFamilyExists,
        AssetExistsInterface $assetExists,
        EditAssetCommandFactory $editAssetCommandFactory,
        EditAssetHandler $editAssetHandler,
        CreateAssetHandler $createAssetHandler,
        Router $router,
        ValidatorInterface $assetDataValidator,
        ViolationNormalizer $violationNormalizer,
        AssetValidator $assetStructureValidator,
        AssetListValidator $assetListValidator,
        int $maximumAssetsPerRequest
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->assetExists = $assetExists;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->editAssetHandler = $editAssetHandler;
        $this->createAssetHandler = $createAssetHandler;
        $this->router = $router;
        $this->assetDataValidator = $assetDataValidator;
        $this->violationNormalizer = $violationNormalizer;
        $this->assetStructureValidator = $assetStructureValidator;
        $this->assetListValidator = $assetListValidator;
        $this->maximumAssetsPerRequest = $maximumAssetsPerRequest;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (!$this->assetFamilyExists->withIdentifier($assetFamilyIdentifier)) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $assetFamilyIdentifier));
        }

        $normalizedAssets = $this->getNormalizedAssetsFromRequest($request);
        $structureErrors = $this->assetListValidator->validate($normalizedAssets);

        if (!empty($structureErrors)) {
            return new JsonResponse([
                'code'    => Response::HTTP_BAD_REQUEST,
                'message' => 'The list of assets has an invalid format.',
                'errors'  => JsonSchemaErrorsFormatter::format($structureErrors),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (count($normalizedAssets) > $this->maximumAssetsPerRequest) {
            return new JsonResponse([
                'code'    => Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                'message' => sprintf('Too many resources to process, %d is the maximum allowed.', $this->maximumAssetsPerRequest),
            ], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        $responsesData = [];
        foreach ($normalizedAssets as $normalizedAsset) {
            try {
                $responseData = $this->createOrUpdateAsset($assetFamilyIdentifier, $normalizedAsset);
            } catch (\InvalidArgumentException $exception) {
                $responseData = [
                    'code'        => $normalizedAsset['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message'     => $exception->getMessage()
                ];
            } catch (ViolationHttpException $exception) {
                $responseData = [
                    'code'        => $normalizedAsset['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
                $responseData += $this->violationNormalizer->normalize($exception);
            }

            $responsesData[] = $responseData;
        }

        return new JsonResponse($responsesData);
    }

    private function getNormalizedAssetsFromRequest(Request $request): array
    {
        $normalizedAssets = json_decode($request->getContent(), true);

        if (null === $normalizedAssets) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedAssets;
    }

    private function createOrUpdateAsset(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAsset): array
    {
        $structureErrors = $this->assetStructureValidator->validate($assetFamilyIdentifier, $normalizedAsset);

        if (!empty($structureErrors)) {
            return [
                'code'        => $normalizedAsset['code'] ?? '',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message'     => 'The asset has an invalid format.',
                'errors'      => JsonSchemaErrorsFormatter::format($structureErrors),
            ];
        }

        $assetCode = AssetCode::fromString($normalizedAsset['code']);
        $shouldBeCreated = !$this->assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
        $createAssetCommand = null;

        if (true === $shouldBeCreated) {
            $createAssetCommand = new CreateAssetCommand(
                $assetFamilyIdentifier->normalize(),
                $normalizedAsset['code'],
                []
            );

            $violations = $this->assetDataValidator->validate($createAssetCommand);
            if ($violations->count() > 0) {
                throw new ViolationHttpException($violations, 'The asset has data that does not comply with the business rules.');
            }
        }

        $editAssetCommand = $this->editAssetCommandFactory->create($assetFamilyIdentifier, $normalizedAsset);

        $violations = $this->assetDataValidator->validate($editAssetCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The asset has data that does not comply with the business rules.');
        }

        if (true === $shouldBeCreated) {
            ($this->createAssetHandler)($createAssetCommand);
        }

        ($this->editAssetHandler)($editAssetCommand);

        return [
            'code' => (string) $assetCode,
            'status_code' => $shouldBeCreated ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT,
        ];
    }
}
