<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute;

use Akeneo\AssetManager\Application\Attribute\AppendAttributeOption\AppendAttributeOptionCommand;
use Akeneo\AssetManager\Application\Attribute\AppendAttributeOption\AppendAttributeOptionHandler;
use Akeneo\AssetManager\Application\Attribute\EditAttributeOption\EditAttributeOptionCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttributeOption\EditAttributeOptionHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeSupportsOptions;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\AttributeOptionValidator;
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

class CreateOrUpdateAttributeOptionAction
{
    private Router $router;

    private AttributeOptionValidator $jsonSchemaValidator;

    private ValidatorInterface $businessRulesValidator;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private AttributeExistsInterface $attributeExists;

    private AttributeSupportsOptions $attributeSupportsOptions;

    private GetAttributeIdentifierInterface $getAttributeIdentifier;

    private AttributeRepositoryInterface $attributeRepository;

    private EditAttributeOptionHandler $editAttributeOptionHandler;

    private AppendAttributeOptionHandler $appendAttributeOptionHandler;

    public function __construct(
        Router $router,
        AttributeOptionValidator $jsonSchemaValidator,
        ValidatorInterface $businessRulesValidator,
        AssetFamilyExistsInterface $assetFamilyExists,
        AttributeExistsInterface $attributeExists,
        AttributeSupportsOptions $attributeSupportsOptions,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeOptionHandler $editAttributeOptionHandler,
        AppendAttributeOptionHandler $appendAttributeOptionHandler
    ) {
        $this->router = $router;
        $this->jsonSchemaValidator = $jsonSchemaValidator;
        $this->businessRulesValidator = $businessRulesValidator;
        $this->assetFamilyExists = $assetFamilyExists;
        $this->attributeExists = $attributeExists;
        $this->attributeSupportsOptions = $attributeSupportsOptions;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->attributeRepository = $attributeRepository;
        $this->editAttributeOptionHandler = $editAttributeOptionHandler;
        $this->appendAttributeOptionHandler = $appendAttributeOptionHandler;
    }

    public function __invoke(Request $request, string $assetFamilyIdentifier, string $attributeCode, string $optionCode): Response
    {
        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
            $attributeCode = AttributeCode::fromString($attributeCode);
            $optionCode = OptionCode::fromString($optionCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $option = json_decode($request->getContent(), true);

        if (null === $option) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        $invalidFormatErrors = $this->jsonSchemaValidator->validate($option);

        if (!empty($invalidFormatErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The attribute option has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($invalidFormatErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ((string) $optionCode !== $option['code']) {
            throw new UnprocessableEntityHttpException('The code of the attribute option provided in the URI must be the same as the one provided in the request body.');
        }

        $assetFamilyExists = $this->assetFamilyExists->withIdentifier($assetFamilyIdentifier);

        if (!$assetFamilyExists) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $assetFamilyIdentifier));
        }

        $attributeExists = $this->attributeExists->withAssetFamilyAndCode($assetFamilyIdentifier, $attributeCode);

        if (!$attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for asset family "%s".',
                (string) $attributeCode,
                (string) $assetFamilyIdentifier
            ));
        }

        $attributeSupportsOptions = $this->attributeSupportsOptions->supports($assetFamilyIdentifier, $attributeCode);

        if (!$attributeSupportsOptions) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not support options.', $attributeCode));
        }

        $optionExists = $this->isOptionExisting($assetFamilyIdentifier, $attributeCode, $optionCode);

        return $optionExists ?
            $this->editOption($assetFamilyIdentifier, $attributeCode, $optionCode, $option) :
            $this->createOption($assetFamilyIdentifier, $attributeCode, $optionCode, $option);
    }

    public function editOption(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        array $option
    ): Response {
        $command = new EditAttributeOptionCommand(
            (string) $assetFamilyIdentifier,
            (string) $attributeCode,
            (string) $optionCode,
            $option['labels'] ?? []
        );

        $violations = $this->businessRulesValidator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The attribute option has data that does not comply with the business rules.');
        }

        ($this->editAttributeOptionHandler)($command);

        $headers = [
            'Location' => $this->router->generate('akeneo_asset_manager_asset_family_attribute_option_rest_connector_get', [
                'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
                'attributeCode' => (string) $attributeCode,
                'optionCode' => (string) $optionCode
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', Response::HTTP_NO_CONTENT, $headers);
    }

    public function createOption(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        array $option
    ): Response {
        $command = new AppendAttributeOptionCommand(
            (string)$assetFamilyIdentifier,
            (string)$attributeCode,
            (string)$optionCode,
            $option['labels'] ?? []
        );

        $violations = $this->businessRulesValidator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The attribute option has data that does not comply with the business rules.');
        }

        ($this->appendAttributeOptionHandler)($command);

        $headers = [
            'Location' => $this->router->generate('akeneo_asset_manager_asset_family_attribute_option_rest_connector_get', [
                'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
                'attributeCode' => (string) $attributeCode,
                'optionCode' => (string) $optionCode
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', Response::HTTP_CREATED, $headers);
    }

    public function isOptionExisting(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode
    ): bool {
        $attributeIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode($assetFamilyIdentifier, $attributeCode);
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);

        return $attribute->hasAttributeOption($optionCode);
    }
}
