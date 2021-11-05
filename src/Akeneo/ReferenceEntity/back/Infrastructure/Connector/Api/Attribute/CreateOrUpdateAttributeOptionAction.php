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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrUpdateAttributeOptionAction
{
    /** @var Router */
    private $router;

    /** @var AttributeOptionValidator */
    private $jsonSchemaValidator;

    /** @var ValidatorInterface */
    private $businessRulesValidator;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var AttributeExistsInterface */
    private $attributeExists;

    /** @var AttributeSupportsOptions */
    private $attributeSupportsOptions;

    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var EditAttributeOptionHandler */
    private $editAttributeOptionHandler;

    /** @var AppendAttributeOptionHandler */
    private $appendAttributeOptionHandler;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    private LoggerInterface $apiAclLogger;

    public function __construct(
        Router $router,
        AttributeOptionValidator $jsonSchemaValidator,
        ValidatorInterface $businessRulesValidator,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AttributeExistsInterface $attributeExists,
        AttributeSupportsOptions $attributeSupportsOptions,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeOptionHandler $editAttributeOptionHandler,
        AppendAttributeOptionHandler $appendAttributeOptionHandler,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->router = $router;
        $this->jsonSchemaValidator = $jsonSchemaValidator;
        $this->businessRulesValidator = $businessRulesValidator;
        $this->referenceEntityExists = $referenceEntityExists;
        $this->attributeExists = $attributeExists;
        $this->attributeSupportsOptions = $attributeSupportsOptions;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->attributeRepository = $attributeRepository;
        $this->editAttributeOptionHandler = $editAttributeOptionHandler;
        $this->appendAttributeOptionHandler = $appendAttributeOptionHandler;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
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

        if (false === $referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $attributeExists = $this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for reference entity "%s".',
                (string) $attributeCode,
                (string) $referenceEntityIdentifier
            ));
        }

        $attributeSupportsOptions = $this->attributeSupportsOptions->supports($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeSupportsOptions) {
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

        return Response::create('', Response::HTTP_NO_CONTENT, $headers);
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

        return Response::create('', Response::HTTP_CREATED, $headers);
    }

    public function isOptionExisting(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode
    ): bool {
        $attributeIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);

        $optionExists = $attribute->hasAttributeOption($optionCode);

        return $optionExists;
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        $acl = 'pim_api_reference_entity_edit';

        if (!$this->securityFacade->isGranted($acl)) {
            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                throw new \LogicException('An user must be authenticated if ACLs are required');
            }

            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                throw new \LogicException(sprintf(
                    'An instance of "%s" is expected if ACLs are required',
                    UserInterface::class
                ));
            }

            $this->apiAclLogger->warning(sprintf(
                'User "%s" with roles %s is not granted "%s"',
                $user->getUsername(),
                implode(',', $user->getRoles()),
                $acl
            ));
        }
    }
}
