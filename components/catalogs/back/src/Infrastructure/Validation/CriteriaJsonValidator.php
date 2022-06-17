<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use JsonSchema\Validator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CriteriaJsonValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CriteriaJson) {
            throw new UnexpectedTypeException($constraint, CriteriaJson::class);
        }

        $jsonSchemaValidator = new Validator();
        $normalizedValueObject = \json_decode(\json_encode($value, JSON_THROW_ON_ERROR));
        $jsonSchemaValidator->validate($normalizedValueObject, $this->getJsonSchema());

        if (!$jsonSchemaValidator->isValid()) {
            foreach ($jsonSchemaValidator->getErrors() as $error) {
                $this->context->buildViolation($error['message'])
                    ->atPath($error['pointer'])
                    ->addViolation();
            }
        }
    }

    /**
     * @return array<mixed>
     */
    private function getJsonSchema(): array
    {
        return [
            'type' => 'array',
            'items' => [
                'oneOf' => [
                    $this->getStatusCriterionJsonSchema(),
                ],
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getStatusCriterionJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'field' => [
                    'type' => 'string',
                    'enum' => ['status'],
                ],
                'operator' => [
                    'type' => 'string',
                    'enum' => ['=', '!='],
                ],
                'value' => [
                    'type' => 'boolean',
                ],
            ],
            'required' => ['field', 'operator', 'value'],
        ];
    }
}
