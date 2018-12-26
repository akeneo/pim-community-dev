<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistry;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
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

    /** @var Router */
    private $router;

    public function __construct(CreateAttributeCommandFactoryRegistry $createAttributeCommandFactoryRegistry, FindAttributeNextOrderInterface $attributeNextOrder, AttributeExistsInterface $attributeExists, CreateAttributeHandler $createAttributeHandler, Router $router)
    {
        $this->createAttributeCommandFactoryRegistry = $createAttributeCommandFactoryRegistry;
        $this->attributeNextOrder = $attributeNextOrder;
        $this->attributeExists = $attributeExists;
        $this->createAttributeHandler = $createAttributeHandler;
        $this->router = $router;
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

        $createAttributeCommand = null;
        $shouldBeCreated = !$this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);
        $createAttributeCommand = null;
        if (true === $shouldBeCreated) {
            $createAttributeCommand = $this->createAttributeCommandFactoryRegistry->getFactory($normalizedAttribute)->create($normalizedAttribute);
            // TODO: This should not be part of the Controller logic
            $createAttributeCommand->order = $this->attributeNextOrder->withReferenceEntityIdentifier(
                ReferenceEntityIdentifier::fromString($createAttributeCommand->referenceEntityIdentifier)
            );
        }

        if (true === $shouldBeCreated) {
            ($this->createAttributeHandler)($createAttributeCommand);
        }

        $headers = [
            'location' => $this->router->generate('akeneo_reference_entities_reference_entity_attribute_rest_connector_get', [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
                'code' => (string) $attributeCode,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        $responseStatusCode = true === $shouldBeCreated ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;

        return Response::create('', $responseStatusCode, $headers);
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
}
