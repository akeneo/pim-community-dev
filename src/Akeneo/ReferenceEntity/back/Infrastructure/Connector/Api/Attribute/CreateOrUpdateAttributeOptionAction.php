<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Application\Attribute\AppendAttributeOption\AppendAttributeOptionCommand;
use Akeneo\ReferenceEntity\Application\Attribute\AppendAttributeOption\AppendAttributeOptionHandler;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption\EditAttributeOptionCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption\EditAttributeOptionHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeSupportsOptions;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\AttributeOptionValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrUpdateAttributeOptionAction
{
    public function __construct(
        private Router $router,
        private AttributeOptionValidator $jsonSchemaValidator,
        private ValidatorInterface $businessRulesValidator,
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private AttributeExistsInterface $attributeExists,
        private AttributeSupportsOptions $attributeSupportsOptions,
        private GetAttributeIdentifierInterface $getAttributeIdentifier,
        private AttributeRepositoryInterface $attributeRepository,
        private EditAttributeOptionHandler $editAttributeOptionHandler,
        private AppendAttributeOptionHandler $appendAttributeOptionHandler,
        private SecurityFacade $securityFacade
    ) {
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $attributeCode, string $optionCode): Response
    {
        $this->denyAccessUnlessAclIsGranted();

        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
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

        $referenceEntityExists = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);

        if (!$referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $attributeExists = $this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        if (!$attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for reference entity "%s".',
                (string) $attributeCode,
                (string) $referenceEntityIdentifier
            ));
        }

        $attributeSupportsOptions = $this->attributeSupportsOptions->supports($referenceEntityIdentifier, $attributeCode);

        if (!$attributeSupportsOptions) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not support options.', $attributeCode));
        }

        $optionExists = $this->isOptionExisting($referenceEntityIdentifier, $attributeCode, $optionCode);

        return $optionExists ?
            $this->editOption($referenceEntityIdentifier, $attributeCode, $optionCode, $option) :
            $this->createOption($referenceEntityIdentifier, $attributeCode, $optionCode, $option);
    }

    public function editOption(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        array $option
    ): Response {
        $command = new EditAttributeOptionCommand(
            (string) $referenceEntityIdentifier,
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
            'Location' => $this->router->generate('akeneo_reference_entities_reference_entity_attribute_option_rest_connector_get', [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
                'attributeCode' => (string) $attributeCode,
                'optionCode' => (string) $optionCode
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return new Response('', Response::HTTP_NO_CONTENT, $headers);
    }

    public function createOption(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode,
        array $option
    ): Response {
        $command = new AppendAttributeOptionCommand(
            (string)$referenceEntityIdentifier,
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
            'Location' => $this->router->generate('akeneo_reference_entities_reference_entity_attribute_option_rest_connector_get', [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
                'attributeCode' => (string) $attributeCode,
                'optionCode' => (string) $optionCode
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return new Response('', Response::HTTP_CREATED, $headers);
    }

    public function isOptionExisting(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode
    ): bool {
        $attributeIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);

        return $attribute->hasAttributeOption($optionCode);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update reference entities.');
        }
    }
}
