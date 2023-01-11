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
            $this->context
                ->buildViolation('You must provide a valid schema.')
                ->addViolation();

            $formatter = new ErrorFormatter();
            $this->logger->debug(
                'A Product Mapping Schema validation failed',
                $formatter->formatOutput($result->error(), 'verbose')
            );
        }
    }

    private function getMetaSchemaLocalPath(string $id): ?string
    {
        return match ($id) {
            'https://api.akeneo.com/mapping/product/0.0.1/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.1.json',
            'https://api.akeneo.com/mapping/product/0.0.2/schema' => __DIR__.'/../Symfony/Resources/meta-schemas/product-0.0.2.json',
            default => null,
        };
    }
}
