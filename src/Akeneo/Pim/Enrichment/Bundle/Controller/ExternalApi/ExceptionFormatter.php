<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\UnknownUserIntentException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
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
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

/**
 * In the /products-uuid endpoints, we now use the Product Service API.
 * This service throws exception when it is not possible to update the product. But the exception messages are not
 * the same than the ones expected by the External API.
 *
 * This class will throw an Exception for the External API from an exception from the Product Service API.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ExceptionFormatter
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private Connection $connection,
    ) {
    }

    public function format(\Throwable $e): void
    {
        if ($e instanceof UnknownUserIntentException) {
            $this->throwDocumentedHttpException(sprintf('Property "%s" does not exist.', $e->getFieldName()), $e);
        }

        if ($e instanceof UnknownAttributeException) {
            $this->throwDocumentedHttpException(sprintf('The %s attribute does not exist in your PIM.', $e->getAttributeCode()), $e);
        }

        if ($e instanceof \InvalidArgumentException) {
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        }

        if ($e instanceof ViolationsException && $e->violations()->get(0)->getConstraint() instanceof AttributeGroupShouldBeEditable) {
            $invalidValue = $e->violations()->get(0)->getInvalidValue();
            Assert::isInstanceOf($invalidValue, ValueUserIntent::class);
            $attribute = $this->attributeRepository->findOneByIdentifier($invalidValue->attributeCode());
            $attributeGroupCode = $attribute->getGroup()->getCode();

            throw new AccessDeniedHttpException(
                sprintf('Attribute "%s" belongs to the attribute group "%s" on which you only have view permission.', $invalidValue->attributeCode(), $attributeGroupCode),
                $e
            );
        }

        if ($e instanceof ViolationsException && $e->violations()->get(0)->getConstraint() instanceof AttributeGroupShouldBeReadable) {
            $invalidValue = $e->violations()->get(0)->getInvalidValue();
            Assert::isInstanceOf($invalidValue, ValueUserIntent::class);
            $this->throwDocumentedHttpException(
                sprintf('The %s attribute does not exist in your PIM.', $invalidValue->attributeCode()),
                $e
            );
        }

        if ($e instanceof ViolationsException && $e->violations()->get(0)->getConstraint() instanceof LocaleShouldBeEditableByUser) {
            $invalidValue = $e->violations()->get(0)->getInvalidValue();
            Assert::isInstanceOf($invalidValue, ValueUserIntent::class);

            throw new AccessDeniedHttpException(
                sprintf('You only have a view permission on the locale "%s".', $invalidValue->localeCode()),
                $e
            );
        }

        if ($e instanceof ViolationsException && $e->violations()->get(0)->getConstraint() instanceof LocaleShouldBeReadableByUser) {
            $invalidValue = $e->violations()->get(0)->getInvalidValue();
            Assert::isInstanceOf($invalidValue, ValueUserIntent::class);

            $this->throwDocumentedHttpException(
                sprintf('Attribute "%s" expects an existing and activated locale, "%s" given.', $invalidValue->attributeCode(), $invalidValue->localeCode()),
                $e
            );
        }

        if ($e instanceof ViolationsException && $e->violations()->get(0)->getConstraint() instanceof CategoriesShouldBeViewable) {
            $violation = $e->violations()->get(0);
            $categoryCodes = $violation->getParameters()['{{ categoryCodes }}'];

            $this->throwDocumentedHttpException(
                sprintf('Property "categories" expects a valid category code. The category does not exist, "%s" given.', $categoryCodes),
                $e
            );
        }

        if ($e instanceof ViolationsException) {
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
        }

        if ($e instanceof LegacyViolationsException) {
            $this->throwViolationExceptionAndReplaceIdentifiersByUuids($e->violations());
        }

        if ($e instanceof InvalidPropertyTypeException) {
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        }

        throw $e;
    }

    private function throwDocumentedHttpException(string $message, \Exception $previousException = null)
    {
        throw new DocumentedHttpException(
            Documentation::URL . 'post_products',
            sprintf('%s Check the expected format on the API documentation.', $message),
            $previousException
        );
    }

    private function getUuidFromIdentifier(string $productIdentifier): ?UuidInterface
    {
        $uuid = $this->connection->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?',
            [$productIdentifier]
        );

        return false === $uuid ? null : Uuid::fromString($uuid);
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
