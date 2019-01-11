<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeSupportsOptions;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\AttributeOptionValidatorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CreateOrUpdateAttributeOptionAction
{
    /** @var AttributeOptionValidatorInterface **/
    private $validator;

    /** @var ReferenceEntityExistsInterface  */
    private $referenceEntityExists;

    /** @var AttributeExistsInterface  */
    private $attributeExists;

    /** @var AttributeSupportsOptions  */
    private $attributeSupportsOptions;

    /** @var FindConnectorAttributeByIdentifierAndCodeInterface */
    private $findConnectorAttributeQuery;

    public function __construct(
        AttributeOptionValidatorInterface $validator,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AttributeExistsInterface $attributeExists,
        AttributeSupportsOptions $attributeSupportsOptions,
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttributeQuery
    ) {
        $this->validator = $validator;
        $this->referenceEntityExists = $referenceEntityExists;
        $this->attributeExists = $attributeExists;
        $this->attributeSupportsOptions = $attributeSupportsOptions;
        $this->findConnectorAttributeQuery = $findConnectorAttributeQuery;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $attributeCode, string $optionCode)
    {
        $invalidFormatErrors = $this->validator->validate(json_decode($request->getContent(), true));

        if (!empty($invalidFormatErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The attribute has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($invalidFormatErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $referenceEntityExists = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);

        if (false === $referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        try {
            $attributeCode = AttributeCode::fromString($attributeCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeExists = $this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for reference entity "%s".',
                (string) $attributeCode,
                (string) $referenceEntityIdentifier
            ));
        }

        $attributeSupportsOptions = ($this->attributeSupportsOptions)($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeSupportsOptions) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not support options.', $attributeCode));
        }

        // TODO - Check to return a hydrated attribute instead
        $attribute = ($this->findConnectorAttributeQuery)($referenceEntityIdentifier, $attributeCode);

        try {
            $optionCode = OptionCode::fromString($optionCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $optionExists = $attribute->hasAttributeOption($optionCode);

        $optionExists ?
            $this->editOption($referenceEntityIdentifier, $attributeCode, $optionCode, $request) :
            $this->createOption($referenceEntityIdentifier, $attributeCode, $optionCode, $request);
    }

    public function editOption(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        Request $request
    ) {
        // Validate the data with json schema validator
        // Use the handler to update
        // AppendAttributeOptionCommand
        // AppendAttributeOptionHandler
        // Normalize the attribute
    }

    public function createOption(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        Request $request
    ) {
    }
}
