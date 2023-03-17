<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type JsonSchemaErrors array<array-key, array{errors?: array<array-key, mixed>, error: string, instanceLocation: string}>
 * @phpstan-import-type ProductMappingSchema from GetProductMappingSchemaQueryInterface as ProductMappingSchemaType
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ProductMappingSchemaValidator extends ConstraintValidator
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductMappingSchema) {
            throw new UnexpectedTypeException($constraint, ProductMappingSchema::class);
        }

        if (!\is_object($value)) {
            throw new UnexpectedValueException($value, 'object');
        }

        $metaSchemaId = $value->{'$schema'} ?? null;
        if (null === $metaSchemaId || !\is_string($metaSchemaId)) {
            $this->context
                ->buildViolation('You must provide a $schema reference.')
                ->addViolation();

            return;
        }

        $metaSchemaPath = $this->getMetaSchemaLocalPath($metaSchemaId);
        if (null === $metaSchemaPath) {
            $this->context
                ->buildViolation('You must provide a valid $schema reference.')
                ->addViolation();

            return;
        }

        $validator = new Validator();
        $resolver = $validator->resolver();
        \assert(null !== $resolver);
        $resolver->registerFile($metaSchemaId, $metaSchemaPath);

        $result = $validator->validate($value, $metaSchemaId);

        if ($result->hasError()) {
            $formatter = new ErrorFormatter();
            /** @var JsonSchemaErrors $errors */
            $errors = $formatter->formatOutput($result->error(), 'verbose')['errors'];

            $this->context
                ->buildViolation('You must provide a valid schema.')
                ->setCause($this->findFirstRecursiveError($errors))
                ->addViolation();

            $this->logger->debug('A Product Mapping Schema validation failed', $errors);

            return;
        }

        if ($this->containsInvalidRegexes($value)) {
            $this->context
                ->buildViolation('You must provide a schema with valid regexes.')
                ->addViolation();

            return;
        }

        if ($this->containsMissingRequiredPropertyKeys($value)) {
            $this->context
                ->buildViolation('You must provide a valid schema.')
                ->setCause('You must provide a schema with valid property keys.')
                ->addViolation();

            return;
        }
    }

    /**
     * @param JsonSchemaErrors $errors
     */
    private function findFirstRecursiveError(array $errors): string
    {
        if (isset($errors[0]['errors'])) {
            /** @var JsonSchemaErrors $childrenErrors */
            $childrenErrors = $errors[0]['errors'];
            return $this->findFirstRecursiveError($childrenErrors);
        }

        return \sprintf('%s at %s', $errors[0]['error'], $errors[0]['instanceLocation']);
    }

    private function getMetaSchemaLocalPath(string $id): ?string
    {
        return match ($id) {
            'https://api.akeneo.com/mapping/product/0.0.1/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.1.json',
            'https://api.akeneo.com/mapping/product/0.0.2/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.2.json',
            'https://api.akeneo.com/mapping/product/0.0.3/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.3.json',
            'https://api.akeneo.com/mapping/product/0.0.4/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.4.json',
            'https://api.akeneo.com/mapping/product/0.0.5/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.5.json',
            'https://api.akeneo.com/mapping/product/0.0.6/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.6.json',
            'https://api.akeneo.com/mapping/product/0.0.7/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.7.json',
            'https://api.akeneo.com/mapping/product/0.0.8/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.8.json',
            'https://api.akeneo.com/mapping/product/0.0.9/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.9.json',
            'https://api.akeneo.com/mapping/product/0.0.10/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.10.json',
            'https://api.akeneo.com/mapping/product/0.0.11/schema' => __DIR__ . '/../Symfony/Resources/meta-schemas/product-0.0.11.json',
            'https://api.akeneo.com/mapping/product/0.0.12/schema' => __DIR__ . '/../Symfony/Resources/meta-schemas/product-0.0.12.json',
            default => null,
        };
    }

    private function containsInvalidRegexes(object $schemaObject): bool
    {
        /** @var ProductMappingSchemaType $schema */
        $schema = \json_decode(\json_encode($schemaObject, JSON_THROW_ON_ERROR) ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        foreach ($schema['properties'] as $property) {
            if (!isset($property['pattern'])) {
                continue;
            }

            if (@\preg_match(\sprintf('/%s/', \preg_quote($property['pattern'], '/')), '') === false) {
                return true;
            }
        }
        return false;
    }

    private function containsMissingRequiredPropertyKeys(object $schemaObject): bool
    {
        /** @var ProductMappingSchemaType $schema */
        $schema = \json_decode(\json_encode($schemaObject, JSON_THROW_ON_ERROR) ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        if (!isset($schema['required'])) {
            return false;
        }

        $propertyKeys = \array_keys($schema['properties']);
        $missingPropertyKeys = \array_diff($schema['required'], $propertyKeys);

        return \count($missingPropertyKeys) > 0;
    }
}
