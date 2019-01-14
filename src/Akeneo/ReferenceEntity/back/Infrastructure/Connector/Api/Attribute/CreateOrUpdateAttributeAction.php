<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistry;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactory;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeCreationValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\AttributeEditionValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
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
    /** @var CreateAttributeCommandFactoryRegistry */
    private $createAttributeCommandFactoryRegistry;

    /** @var FindAttributeNextOrderInterface */
    private $attributeNextOrder;

    /** @var AttributeExistsInterface */
    private $attributeExists;

    /** @var CreateAttributeHandler */
    private $createAttributeHandler;

    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    /** @var EditAttributeCommandFactory */
    private $editAttributeCommandFactory;

    /** @var EditAttributeHandler */
    private $editAttributeHandler;

    /** @var Router */
    private $router;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var ValidatorInterface */
    private $validator;

    /** @var AttributeCreationValidator */
    private $jsonSchemaCreateValidator;

    /** @var AttributeEditionValidator */
    private $jsonSchemaEditValidator;

    public function __construct(
        CreateAttributeCommandFactoryRegistry $createAttributeCommandFactoryRegistry,
        FindAttributeNextOrderInterface $attributeNextOrder,
        AttributeExistsInterface $attributeExists,
        CreateAttributeHandler $createAttributeHandler,
        Router $router,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        EditAttributeCommandFactory $editAttributeCommandFactory,
        EditAttributeHandler $editAttributeHandler,
        ReferenceEntityExistsInterface $referenceEntityExists,
        ValidatorInterface $validator,
        AttributeCreationValidator $jsonSchemaCreateValidator,
        AttributeEditionValidator $jsonSchemaEditValidator
    ) {
        $this->createAttributeCommandFactoryRegistry = $createAttributeCommandFactoryRegistry;
        $this->attributeNextOrder = $attributeNextOrder;
        $this->attributeExists = $attributeExists;
        $this->createAttributeHandler = $createAttributeHandler;
        $this->router = $router;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->editAttributeCommandFactory = $editAttributeCommandFactory;
        $this->editAttributeHandler = $editAttributeHandler;
        $this->referenceEntityExists = $referenceEntityExists;
        $this->validator = $validator;
        $this->jsonSchemaCreateValidator = $jsonSchemaCreateValidator;
        $this->jsonSchemaEditValidator = $jsonSchemaEditValidator;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $attributeCode): Response
    {
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
            $attributeCode = AttributeCode::fromString($attributeCode);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $normalizedAttribute = json_decode($request->getContent(), true);
        if (null === $normalizedAttribute) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        $inBodyAttributeCode = $normalizedAttribute['code'] ?? null;
        if ((string) $attributeCode !== $inBodyAttributeCode) {
            throw new UnprocessableEntityHttpException('The code of the reference entity provided in the URI must be the same as the one provided in the request body.');
        }

        $shouldBeCreated = !$this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        return $shouldBeCreated ?
            $this->createAttribute($referenceEntityIdentifier, $attributeCode, $normalizedAttribute) :
            $this->editAttribute($referenceEntityIdentifier, $attributeCode, $normalizedAttribute);
    }

    /**
     * The format between the API and the UI is no the same.
     * Ideally, we should do a factory for this API adapter.
     */
    private function getNormalizedAttribute(
        array $normalizedAttribute,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ) {
        $normalizedAttribute['reference_entity_identifier'] = (string) $referenceEntityIdentifier;

        if (isset($normalizedAttribute['validation_regexp'])) {
            $normalizedAttribute['regular_expression'] = $normalizedAttribute['validation_regexp'];
            unset($normalizedAttribute['validation_regexp']);
        }

        if (isset($normalizedAttribute['is_required_for_completeness'])) {
            $normalizedAttribute['is_required'] = $normalizedAttribute['is_required_for_completeness'];
            unset($normalizedAttribute['is_required_for_completeness']);
        }

        if (isset($normalizedAttribute['is_required_for_completeness'])) {
            $normalizedAttribute['is_required'] = $normalizedAttribute['is_required_for_completeness'];
            unset($normalizedAttribute['is_required_for_completeness']);
        }

        if (isset($normalizedAttribute['max_characters'])) {
            $normalizedAttribute['max_length'] = $normalizedAttribute['max_characters'];
            unset($normalizedAttribute['max_characters']);
        }

        if (isset($normalizedAttribute['type'])) {
            switch ($normalizedAttribute['type']) {
                case 'single_option':
                    $normalizedAttribute['type'] = 'option';
                    break;
                case 'multiple_options':
                    $normalizedAttribute['type'] = 'option_collection';
                    break;
                case 'reference_entity_single_link':
                    $normalizedAttribute['type'] = 'record';
                    break;
                case 'reference_entity_multiple_links':
                    $normalizedAttribute['type'] = 'record_collection';
                    break;
            }
        }

        $normalizedAttribute['reference_entity_identifier'] = (string) $referenceEntityIdentifier;

        return $normalizedAttribute;
    }

    private function createAttribute(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
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

        $normalizedAttribute = $this->getNormalizedAttribute($normalizedAttribute, $referenceEntityIdentifier);

        $createAttributeCommand = $this->createAttributeCommandFactoryRegistry->getFactory($normalizedAttribute)->create($normalizedAttribute);
        // TODO: This should not be part of the Controller logic
        $createAttributeCommand->order = $this->attributeNextOrder->withReferenceEntityIdentifier(
            ReferenceEntityIdentifier::fromString($createAttributeCommand->referenceEntityIdentifier)
        );

        $violations = $this->validator->validate($createAttributeCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The attribute has data that does not comply with the business rules.');
        }

        ($this->createAttributeHandler)($createAttributeCommand);

        $headers = [
            'Location' => $this->router->generate('akeneo_reference_entities_reference_entity_attribute_rest_connector_get', [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
                'code' => (string) $attributeCode,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', Response::HTTP_CREATED, $headers);
    }

    private function editAttribute(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        array $normalizedAttribute
    ): Response {
        $invalidFormatErrors = $this->jsonSchemaEditValidator->validate(
            $referenceEntityIdentifier,
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

        $normalizedAttribute = $this->getNormalizedAttribute($normalizedAttribute, $referenceEntityIdentifier);
        $normalizedAttribute['identifier'] = (string) $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            $attributeCode
        );
        $editAttributeCommand = $this->editAttributeCommandFactory->create($normalizedAttribute);

        $violations = $this->validator->validate($editAttributeCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The attribute has data that does not comply with the business rules.');
        }

        ($this->editAttributeHandler)($editAttributeCommand);

        $headers = [
            'Location' => $this->router->generate('akeneo_reference_entities_reference_entity_attribute_rest_connector_get', [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
                'code' => (string) $attributeCode,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', Response::HTTP_NO_CONTENT, $headers);
    }
}
