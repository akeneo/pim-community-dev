<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

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
            'https://api.akeneo.com/mapping/product/0.0.6/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.6.json',
            default => null,
        };
    }
}
