<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

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
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

final class ServiceAPIProductUpdater implements ProductUpdater
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private UserContext $userContext,
        private MessageBusInterface $commandMessageBus,
        private MessageBusInterface $queryMessageBus,
        private Connection $connection,
    ) {
    }

    public function update(array $data): void
    {
        try {
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
                $attribute = $this->attributeRepository->findOneByIdentifier($invalidValue->attributeCode());
                $attributeGroupCode = $attribute->getGroup()->getCode();

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

    private function throwDocumentedHttpException(string $message, \Exception $previousException = null)
    {
        throw new DocumentedHttpException(
            Documentation::URL . 'post_products',
            sprintf('%s Check the expected format on the API documentation.', $message),
            $previousException
        );
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

    private function getUuidFromIdentifier(string $productIdentifier): ?UuidInterface
    {
        $uuid = $this->connection->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?',
            [$productIdentifier]
        );

        return false === $uuid ? null : Uuid::fromString($uuid);
    }

    private function getProductIdentifier(array $data): ?string
    {
        return $data['values'][$this->attributeRepository->getIdentifierCode()][0]['data'] ?? null;
    }
}
