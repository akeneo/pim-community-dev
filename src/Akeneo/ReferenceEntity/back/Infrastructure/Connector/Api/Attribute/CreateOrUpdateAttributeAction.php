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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

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

    public function __construct(
        CreateAttributeCommandFactoryRegistry $createAttributeCommandFactoryRegistry,
        FindAttributeNextOrderInterface $attributeNextOrder,
        AttributeExistsInterface $attributeExists,
        CreateAttributeHandler $createAttributeHandler,
        Router $router,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        EditAttributeCommandFactory $editAttributeCommandFactory,
        EditAttributeHandler $editAttributeHandler
    ) {
        $this->createAttributeCommandFactoryRegistry = $createAttributeCommandFactoryRegistry;
        $this->attributeNextOrder = $attributeNextOrder;
        $this->attributeExists = $attributeExists;
        $this->createAttributeHandler = $createAttributeHandler;
        $this->router = $router;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->editAttributeCommandFactory = $editAttributeCommandFactory;
        $this->editAttributeHandler = $editAttributeHandler;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $attributeCode): Response
    {
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
            $attributeCode = AttributeCode::fromString($attributeCode);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $normalizedAttribute = $this->getNormalizedAttribute($request, $referenceEntityIdentifier);
        $shouldBeCreated = !$this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        return $shouldBeCreated ?
            $this->createAttribute($referenceEntityIdentifier, $attributeCode, $normalizedAttribute) :
            $this->editAttribute($referenceEntityIdentifier, $attributeCode, $normalizedAttribute);
    }

    private function getNormalizedAttribute(
        Request $request,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ) {
        $normalizedAttribute = json_decode($request->getContent(), true);
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

        $normalizedAttribute['reference_entity_identifier'] = (string) $referenceEntityIdentifier;

        return $normalizedAttribute;
    }

    private function createAttribute(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        array $normalizedAttribute
    ): Response {
        $createAttributeCommand = $this->createAttributeCommandFactoryRegistry->getFactory($normalizedAttribute)->create($normalizedAttribute);
        // TODO: This should not be part of the Controller logic
        $createAttributeCommand->order = $this->attributeNextOrder->withReferenceEntityIdentifier(
            ReferenceEntityIdentifier::fromString($createAttributeCommand->referenceEntityIdentifier)
        );

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
        $normalizedAttribute['identifier'] = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            $attributeCode
        );
        $editAttributeCommand = $this->editAttributeCommandFactory->getFactory($normalizedAttribute)->create($normalizedAttribute);

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
