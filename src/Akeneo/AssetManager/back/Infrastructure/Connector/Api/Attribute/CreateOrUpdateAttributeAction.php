<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistry;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactory;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeCreationValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\AttributeEditionValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
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

class CreateOrUpdateAttributeAction
{
    private CreateAttributeCommandFactoryRegistry $createAttributeCommandFactoryRegistry;

    private FindAttributeNextOrderInterface $attributeNextOrder;

    private AttributeExistsInterface $attributeExists;

    private CreateAttributeHandler $createAttributeHandler;

    private GetAttributeIdentifierInterface $getAttributeIdentifier;

    private EditAttributeCommandFactory $editAttributeCommandFactory;

    private EditAttributeHandler $editAttributeHandler;

    private Router $router;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private ValidatorInterface $validator;

    private AttributeCreationValidator $jsonSchemaCreateValidator;

    private AttributeEditionValidator $jsonSchemaEditValidator;

    private ValidateAttributePropertiesImmutability $validateAttributePropertiesImmutability;

    public function __construct(
        CreateAttributeCommandFactoryRegistry $createAttributeCommandFactoryRegistry,
        FindAttributeNextOrderInterface $attributeNextOrder,
        AttributeExistsInterface $attributeExists,
        CreateAttributeHandler $createAttributeHandler,
        Router $router,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        EditAttributeCommandFactory $editAttributeCommandFactory,
        EditAttributeHandler $editAttributeHandler,
        AssetFamilyExistsInterface $assetFamilyExists,
        ValidatorInterface $validator,
        AttributeCreationValidator $jsonSchemaCreateValidator,
        AttributeEditionValidator $jsonSchemaEditValidator,
        ValidateAttributePropertiesImmutability $validateAttributePropertiesImmutability
    ) {
        $this->createAttributeCommandFactoryRegistry = $createAttributeCommandFactoryRegistry;
        $this->attributeNextOrder = $attributeNextOrder;
        $this->attributeExists = $attributeExists;
        $this->createAttributeHandler = $createAttributeHandler;
        $this->router = $router;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->editAttributeCommandFactory = $editAttributeCommandFactory;
        $this->editAttributeHandler = $editAttributeHandler;
        $this->assetFamilyExists = $assetFamilyExists;
        $this->validator = $validator;
        $this->jsonSchemaCreateValidator = $jsonSchemaCreateValidator;
        $this->jsonSchemaEditValidator = $jsonSchemaEditValidator;
        $this->validateAttributePropertiesImmutability = $validateAttributePropertiesImmutability;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier, string $attributeCode): Response
    {
        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
            $attributeCode = AttributeCode::fromString($attributeCode);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (!$this->assetFamilyExists->withIdentifier($assetFamilyIdentifier)) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $assetFamilyIdentifier));
        }

        $normalizedAttribute = json_decode($request->getContent(), true);
        if (null === $normalizedAttribute) {
            throw new BadRequestHttpException('Invalid json message received');
        }
        $inBodyAttributeCode = $normalizedAttribute['code'] ?? null;
        if ((string) $attributeCode !== $inBodyAttributeCode) {
            throw new UnprocessableEntityHttpException('The code of the asset family provided in the URI must be the same as the one provided in the request body.');
        }

        $shouldBeCreated = !$this->attributeExists->withAssetFamilyAndCode($assetFamilyIdentifier, $attributeCode);

        return $shouldBeCreated ?
            $this->createAttribute($assetFamilyIdentifier, $attributeCode, $normalizedAttribute) :
            $this->editAttribute($assetFamilyIdentifier, $attributeCode, $normalizedAttribute);
    }

    /**
     * The format between the API and the UI is no the same.
     * Ideally, we should do a factory for this API adapter.
     */
    private function getNormalizedAttribute(
        array $normalizedAttribute,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ) {
        $normalizedAttribute['asset_family_identifier'] = (string) $assetFamilyIdentifier;

        if (array_key_exists('validation_regexp', $normalizedAttribute)) {
            $normalizedAttribute['regular_expression'] = $normalizedAttribute['validation_regexp'];
            unset($normalizedAttribute['validation_regexp']);
        }

        if (array_key_exists('is_required_for_completeness', $normalizedAttribute)) {
            $normalizedAttribute['is_required'] = $normalizedAttribute['is_required_for_completeness'];
            unset($normalizedAttribute['is_required_for_completeness']);
        }

        if (array_key_exists('max_characters', $normalizedAttribute)) {
            $normalizedAttribute['max_length'] = $normalizedAttribute['max_characters'];
            unset($normalizedAttribute['max_characters']);
        }

        if (array_key_exists('asset_family_code', $normalizedAttribute)) {
            $normalizedAttribute['asset_type'] = $normalizedAttribute['asset_family_code'];
            unset($normalizedAttribute['asset_family_code']);
        }

        if (isset($normalizedAttribute['type'])) {
            switch ($normalizedAttribute['type']) {
                case 'single_option':
                    $normalizedAttribute['type'] = 'option';
                    break;
                case 'multiple_options':
                    $normalizedAttribute['type'] = 'option_collection';
                    break;
                case 'asset_family_single_link':
                    $normalizedAttribute['type'] = 'asset';
                    break;
                case 'asset_family_multiple_links':
                    $normalizedAttribute['type'] = 'asset_collection';
                    break;
            }
        }

        $normalizedAttribute['asset_family_identifier'] = (string) $assetFamilyIdentifier;

        return $normalizedAttribute;
    }

    private function createAttribute(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        array $normalizedAttribute
    ): Response {
        $invalidFormatErrors = $this->jsonSchemaCreateValidator->validate($normalizedAttribute);

        if (!empty($invalidFormatErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The attribute has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($invalidFormatErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $normalizedAttribute = $this->getNormalizedAttribute($normalizedAttribute, $assetFamilyIdentifier);

        $createAttributeCommand = $this->createAttributeCommandFactoryRegistry->getFactory($normalizedAttribute)->create($normalizedAttribute);

        $violations = $this->validator->validate($createAttributeCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The attribute has data that does not comply with the business rules.');
        }

        ($this->createAttributeHandler)($createAttributeCommand);

        $headers = [
            'Location' => $this->router->generate('akeneo_asset_manager_asset_family_attribute_rest_connector_get', [
                'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
                'code' => (string) $attributeCode,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', Response::HTTP_CREATED, $headers);
    }

    private function editAttribute(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        array $normalizedAttribute
    ): Response {
        $invalidFormatErrors = $this->jsonSchemaEditValidator->validate(
            $assetFamilyIdentifier,
            $attributeCode,
            $normalizedAttribute
        );

        if (!empty($invalidFormatErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The attribute has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($invalidFormatErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invalidImmutablePropertiesErrors = ($this->validateAttributePropertiesImmutability)(
            $assetFamilyIdentifier,
            $attributeCode,
            $normalizedAttribute
        );

        if (!empty($invalidImmutablePropertiesErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The attribute has data that does not comply with the business rules.',
                'errors' => $invalidImmutablePropertiesErrors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $normalizedAttribute = $this->getNormalizedAttribute($normalizedAttribute, $assetFamilyIdentifier);
        $normalizedAttribute['identifier'] = (string) $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            $attributeCode
        );
        $editAttributeCommand = $this->editAttributeCommandFactory->create($normalizedAttribute);

        $violations = $this->validator->validate($editAttributeCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The attribute has data that does not comply with the business rules.');
        }

        ($this->editAttributeHandler)($editAttributeCommand);

        $headers = [
            'Location' => $this->router->generate('akeneo_asset_manager_asset_family_attribute_rest_connector_get', [
                'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
                'code' => (string) $attributeCode,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', Response::HTTP_NO_CONTENT, $headers);
    }
}
