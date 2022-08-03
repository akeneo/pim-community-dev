<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\UnknownUserIntentException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\AttributeGroupShouldBeEditable;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\AttributeGroupShouldBeReadable;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\CategoriesShouldBeViewable;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeEditableByUser;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeReadableByUser;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductByUuidController
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private UrlGeneratorInterface $router,
        private EventDispatcherInterface $eventDispatcher,
        private DuplicateValueChecker $duplicateValueChecker,
        private SecurityFacade $security,
        private ValidatorInterface $validator,
        private UserContext $userContext,
        private MessageBusInterface $commandMessageBus,
        private MessageBusInterface $queryMessageBus,
        private Connection $connection
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->security->isGranted('pim_api_product_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update products.');
        }

        $data = $this->getDecodedContent($request->getContent());
        $violations = $this->validator->validate($data, new PayloadFormat());
        if (0 < $violations->count()) {
            $firstViolation = $violations->get(0);
            $this->throwDocumentedHttpException($firstViolation->getMessage(), new \LogicException($firstViolation->getMessage()));
        }

        if (!isset($data['values'][$this->attributeRepository->getIdentifierCode()][0]['data'])) {
            $this->throwViolationException('The identifier attribute cannot be empty.', 'identifier');
        }

        try {
            $this->duplicateValueChecker->check($data);
        } catch (InvalidPropertyTypeException $e) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($e));
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        }


        if ($this->productAlreadyExists($data)) {
            $this->throwViolationException(
                sprintf('The %s identifier is already used for another product.', $this->getProductIdentifier($data)),
                'identifier'
            );
        }

        try {
            $data = $this->replaceUuidsByIdentifiers($data);
            $this->updateProduct($data);
        } catch (UnknownUserIntentException $e) {
            $this->throwDocumentedHttpException(sprintf('Property "%s" does not exist.', $e->getFieldName()), $e);
        } catch (UnknownAttributeException $e) {
            $this->throwDocumentedHttpException(sprintf('The %s attribute does not exist in your PIM.', $e->getAttributeCode()), $e);
        } catch (\InvalidArgumentException $e) {
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        } catch (ViolationsException $e) {
            $firstConstraint = $e->violations()->get(0)->getConstraint();
            if ($firstConstraint instanceof AttributeGroupShouldBeEditable) {
                $invalidValue = $e->violations()->get(0)->getInvalidValue();
                Assert::isInstanceOf($invalidValue, ValueUserIntent::class);
                $attributeGroupCode = 'attributeGroupB'; // I have no idea how to get this

                throw new AccessDeniedHttpException(
                    sprintf('Attribute "%s" belongs to the attribute group "%s" on which you only have view permission.', $invalidValue->attributeCode(), $attributeGroupCode),
                    $e
                );
            } elseif ($firstConstraint instanceof AttributeGroupShouldBeReadable) {
                $invalidValue = $e->violations()->get(0)->getInvalidValue();
                Assert::isInstanceOf($invalidValue, ValueUserIntent::class);
                $this->throwDocumentedHttpException(
                    sprintf('The %s attribute does not exist in your PIM.', $invalidValue->attributeCode()),
                    $e
                );
            } elseif ($firstConstraint instanceof LocaleShouldBeEditableByUser) {
                $invalidValue = $e->violations()->get(0)->getInvalidValue();
                Assert::isInstanceOf($invalidValue, ValueUserIntent::class);

                throw new AccessDeniedHttpException(
                    sprintf('You only have a view permission on the locale "%s".', $invalidValue->localeCode()),
                    $e
                );
            } elseif ($firstConstraint instanceof LocaleShouldBeReadableByUser) {
                $invalidValue = $e->violations()->get(0)->getInvalidValue();
                Assert::isInstanceOf($invalidValue, ValueUserIntent::class);

                $this->throwDocumentedHttpException(
                    sprintf('Attribute "%s" expects an existing and activated locale, "%s" given.', $invalidValue->attributeCode(), $invalidValue->localeCode()),
                    $e
                );
            } elseif ($firstConstraint instanceof CategoriesShouldBeViewable) {
                $violation = $e->violations()->get(0);
                $categoryCodes = $violation->getParameters()['{{ categoryCodes }}'];

                $this->throwDocumentedHttpException(
                    sprintf('Property "categories" expects a valid category code. The category does not exist, "%s" given.', $categoryCodes),
                    $e
                );
            }

            $message = $e->violations()->get(0)->getMessage();
            $matches = [];
            if (preg_match('/^Property "associations" expects a valid product identifier. The product does not exist, "(?P<identifier>.*)" given.$/', $message, $matches)) {
                $this->throwDocumentedHttpException(
                    sprintf(
                        'Property "associations" expects a valid product uuid. The product does not exist, "%s" given.',
                        $this->getUuidFromIdentifier($matches['identifier'])
                    ),
                    $e
                );
            }

            $this->throwDocumentedHttpException($e->violations()->get(0)->getMessage(), $e);
        } catch (LegacyViolationsException $e) {
            $this->throwViolationExceptionAndReplaceIdentifiersByUuids($e->violations());
        } catch (InvalidPropertyTypeException $e) {
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        }

        return $this->getResponse($this->getUuidFromIdentifier($this->getProductIdentifier($data)), Response::HTTP_CREATED);
    }
    private function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    private function updateProduct(array $data): void
    {
        $envelope = $this->queryMessageBus->dispatch(new GetUserIntentsFromStandardFormat($data));
        $handledStamp = $envelope->last(HandledStamp::class);
        $userIntents = $handledStamp->getResult();

        $userId = $this->userContext->getUser()?->getId();
        $command = UpsertProductCommand::createFromCollection(
            $userId,
            $this->getProductIdentifier($data),
            $userIntents
        );
        $this->commandMessageBus->dispatch($command);
    }

    private function getResponse(UuidInterface $uuid, int $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_product_uuid_get',
            ['uuid' => $uuid->toString()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }

    private function getProductIdentifier(array $data): ?string
    {
        return $data['values'][$this->attributeRepository->getIdentifierCode()][0]['data'] ?? null;
    }

    private function getUuidFromIdentifier(string $productIdentifier): ?UuidInterface
    {
        $uuid = $this->connection->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?',
            [$productIdentifier]
        );

        return false === $uuid ? null : Uuid::fromString($uuid);
    }

    /**
     * @param string[] $uuidAsStrings
     * @param string $association 'associations'|'quantified_associations'
     * @return array<string, string>
     */
    private function getProductIdentifierFromUuids(array $uuidAsStrings, string $association): array
    {
        $uuidsAsBytes = array_map(fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), $uuidAsStrings);

        $result = $this->connection->fetchAllKeyValue(
            'SELECT BIN_TO_UUID(uuid) AS uuid, identifier FROM pim_catalog_product WHERE uuid IN(:uuids)',
            ['uuids' => $uuidsAsBytes],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        );

        $diff = \array_diff($uuidAsStrings, \array_keys($result));
        if (count($diff) > 0) {
            $this->throwDocumentedHttpException(
                sprintf(
                    'Property "%s" expects a valid product uuid. The product does not exist, "%s" given.',
                    $association,
                    $diff[0]
                )
            );
        }

        return $result;
    }

    private function throwDocumentedHttpException(string $message, \Exception $previousException = null)
    {
        throw new DocumentedHttpException(
            Documentation::URL . 'post_products',
            sprintf('%s Check the expected format on the API documentation.', $message),
            $previousException
        );
    }

    private function throwViolationException(string $message, string $propertyPath): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation($message, $message, [], null, $propertyPath, null),
        ]);

        throw new ViolationHttpException($list);
    }

    /**
     * This method is temporary until Public Service API manages UUIDS
     * @TODO CPM-697
     */
    private function replaceUuidsByIdentifiers(array $data)
    {
        if (isset($data['associations'])) {
            foreach ($data['associations'] as $associationCode => $associations) {
                if (isset($associations['products'])) {
                    $data['associations'][$associationCode]['products'] = \array_values($this->getProductIdentifierFromUuids($associations['products'], 'associations'));
                }
            }
        }

        if (isset($data['quantified_associations'])) {
            foreach ($data['quantified_associations'] as $associationCode => $associations) {
                if (isset($associations['products'])) {
                    $map = $this->getProductIdentifierFromUuids(\array_map(fn (array $association): string => $association['uuid'], $associations['products']), 'quantified_associations');
                    $data['quantified_associations'][$associationCode]['products'] = \array_map(function ($association) use ($map) {
                        return ['quantity' => $association['quantity'], 'identifier' => $map[$association['uuid']]];
                    }, $associations['products']);
                }
            }
        }

        return $data;
    }

    private function productAlreadyExists(array $data): bool
    {
        return null !== $this->getUuidFromIdentifier($this->getProductIdentifier($data));
    }

    private function throwViolationExceptionAndReplaceIdentifiersByUuids(ConstraintViolationListInterface $violations): void
    {
        $newViolations = new ConstraintViolationList();
        foreach ($violations as $violation) {
            $messageTemplate = $violation->getMessageTemplate();
            if ($messageTemplate === 'pim_catalog.constraint.quantified_associations.products_do_not_exist') {
                $parameters = $violation->getParameters();
                $uuid = $this->getUuidFromIdentifier($parameters['{{ values }}']);
                $parameters = ['{{values }}' => $uuid->toString()];
                $message = sprintf('The following products don\'t exist: %s. Please make sure the products haven\'t been deleted in the meantime.', $uuid->toString());

                $newViolations->add(
                    new ConstraintViolation(
                        $message,
                        $messageTemplate,
                        $parameters,
                        $violation->getRoot(),
                        $violation->getPropertyPath(),
                        $violation->getInvalidValue(),
                        $violation->getPlural(),
                        $violation->getCode(),
                        $violation->getConstraint(),
                        $violation->getCause()
                    )
                );
            } else {
                $newViolations->add($violation);
            }
        }

        throw new ViolationHttpException($newViolations);
    }
}
