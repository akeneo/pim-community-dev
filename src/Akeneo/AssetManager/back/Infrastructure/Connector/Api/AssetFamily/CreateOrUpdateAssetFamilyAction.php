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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\File\FindFileDataByFileKeyInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\JsonSchema\AssetFamilyValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrUpdateAssetFamilyAction
{
    private AssetFamilyExistsInterface $assetFamilyExists;

    private ValidatorInterface $validator;

    private CreateAssetFamilyHandler $createAssetFamilyHandler;

    private EditAssetFamilyHandler $editAssetFamilyHandler;

    private Router $router;

    private AssetFamilyValidator $jsonSchemaValidator;

    private FindFileDataByFileKeyInterface $findFileData;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function __construct(
        AssetFamilyExistsInterface $assetFamilyExists,
        ValidatorInterface $validator,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        EditAssetFamilyHandler $editAssetFamilyHandler,
        Router $router,
        AssetFamilyValidator $jsonSchemaValidator,
        FindFileDataByFileKeyInterface $findFileData,
        AssetFamilyRepositoryInterface $assetFamilyRepository
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->validator = $validator;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->router = $router;
        $this->jsonSchemaValidator = $jsonSchemaValidator;
        $this->findFileData = $findFileData;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier): Response
    {
        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $normalizedAssetFamily = $this->getNormalizedAssetFamily($request->getContent());
        $invalidFormatErrors = $this->validateAssetFamilyFormat($assetFamilyIdentifier, $normalizedAssetFamily);

        if (!empty($invalidFormatErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The asset family has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($invalidFormatErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $createAssetFamilyCommand = null;
        $shouldBeCreated = $this->shouldAssetFamilyBeCreated($assetFamilyIdentifier);
        if ($shouldBeCreated) {
            $createAssetFamilyCommand = new CreateAssetFamilyCommand(
                $normalizedAssetFamily['code'],
                $normalizedAssetFamily['labels'] ?? [],
                $normalizedAssetFamily['product_link_rules'] ?? [],
                $normalizedAssetFamily['transformations'] ?? [],
                $normalizedAssetFamily['naming_convention'] ?? []
            );

            $violations = $this->validator->validate($createAssetFamilyCommand);
            if ($violations->count() > 0) {
                throw new ViolationHttpException($violations, 'The asset family has data that does not comply with the business rules.');
            }
        }

        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            $normalizedAssetFamily['code'],
            $normalizedAssetFamily['labels'] ?? [],
            null,
            $normalizedAssetFamily['attribute_as_main_media'] ?? null,
            $normalizedAssetFamily['product_link_rules'] ?? null,
            $normalizedAssetFamily['transformations'] ?? null,
            $normalizedAssetFamily['naming_convention'] ?? null
        );

        if (array_key_exists('image', $normalizedAssetFamily)) {
            $editAssetFamilyCommand->image = null !== $normalizedAssetFamily['image'] ? $this->getImageData($normalizedAssetFamily['image']) : null;
        } elseif (!$shouldBeCreated) {
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
            $editAssetFamilyCommand->image = $assetFamily->getImage()->normalize();
        }

        $violations = $this->validator->validate($editAssetFamilyCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The asset family has data that does not comply with the business rules.');
        }

        if ($shouldBeCreated) {
            ($this->createAssetFamilyHandler)($createAssetFamilyCommand);
        }

        ($this->editAssetFamilyHandler)($editAssetFamilyCommand);

        $headers = [
            'location' => $this->router->generate('akeneo_asset_manager_asset_family_rest_connector_get', [
                'code' => (string) $assetFamilyIdentifier,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        $responseStatusCode = $shouldBeCreated ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;

        return Response::create('', $responseStatusCode, $headers);
    }

    private function shouldAssetFamilyBeCreated(AssetFamilyIdentifier $assetFamilyIdentifier): bool
    {
        try {
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        } catch (AssetFamilyNotFoundException $e) {
            return true;
        }

        if (!$assetFamily->getIdentifier()->equals($assetFamilyIdentifier)) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The "%s" asset family already exists. Creating an asset family with a different case is forbidden.',
                (string) $assetFamily->getIdentifier()
            ));
        }
        return false;
    }

    private function getNormalizedAssetFamily(string $content): array
    {
        $normalizedAssetFamily = json_decode($content, true);
        if (null === $normalizedAssetFamily) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedAssetFamily;
    }

    private function validateAssetFamilyFormat(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $normalizedAssetFamily
    ): array {
        $invalidFormatErrors = $this->jsonSchemaValidator->validate($normalizedAssetFamily);

        if (empty($invalidFormatErrors)) {
            $inBodyAssetFamilyIdentifier = $normalizedAssetFamily['code'] ?? null;
            if ((string) $assetFamilyIdentifier !== $inBodyAssetFamilyIdentifier) {
                throw new UnprocessableEntityHttpException('The code of the asset family provided in the URI must be the same as the one provided in the request body.');
            }
        }

        return $invalidFormatErrors;
    }

    private function getImageData(string $imageFileKey): array
    {
        $imageData = $this->findFileData->find($imageFileKey);

        if (null === $imageData) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The image "%s" was not found', $imageFileKey
            ));
        }

        return [
            'filePath' => $imageData['filePath'],
            'originalFilename' => $imageData['originalFilename'],
        ];
    }
}
