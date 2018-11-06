<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Akeneo\ReferenceEntity\Infrastructure\Validation\ReferenceEntity\LabelCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptionsValidator extends ConstraintValidator
{
    /** @var int */
    private $optionsLimit;

    public function __construct(int $optionsLimit)
    {
        $this->optionsLimit = $optionsLimit;
    }

    public function validate($attributeOptions, Constraint $constraint)
    {
        if (!$constraint instanceof AttributeOptions) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        if ($this->isNotArray($attributeOptions)) {
            return;
        }

        if ($this->hasTooManyOptions($attributeOptions)) {
            return;
        }

        if ($this->checkOptionsAreValid($attributeOptions)) {
            return;
        }

        $this->checkDuplicates($attributeOptions);
    }

    private function addViolationsIfAny(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
        }
    }

    private function isNotArray(array $attributeOptions): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($attributeOptions, [new Assert\Type('array')]);
        $this->addViolationsIfAny($violations);

        return $violations->count() > 0;
    }

    private function hasTooManyOptions($attributeOptions): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $attributeOptions,
            [
                new Assert\Count([
                    'max'        => $this->optionsLimit,
                    'maxMessage' => AttributeOptions::MESSAGE_TOO_MANY_OPTIONS,
                    'min'        => 0,
                ]),
            ]
        );
        $this->addViolationsIfAny($violations);

        return $violations->count() > 0;
    }

    private function checkOptionsAreValid(array $attributeOptions): bool
    {
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();

        foreach ($attributeOptions as $attributeOption) {
            $violations->addAll($validator->validate($attributeOption['code'], new AttributeOptionCode()));
            $violations->addAll($validator->validate($attributeOption['labels'], new LabelCollection()));

            foreach ($violations as $violation) {
                $path = $attributeOption['code'] ?? '';
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath((string) $path)
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural())
                    ->setInvalidValue($violation->getInvalidValue())
                    ->addViolation();
            }
        }

        return 0 !== $violations->count();
    }

    private function checkDuplicates($attributeOptions): void
    {
        $optionCodes = array_map(function (array $attributeOption) {
            return $attributeOption['code'];
        }, $attributeOptions);

        $frequencies = array_count_values($optionCodes);
        foreach ($frequencies as $optionCode => $frequency) {
            if ($frequency > 1) {
                $this->context->buildViolation(AttributeOptions::MESSAGE_OPTION_DUPLICATED)
                    ->setParameter('%option_code%', $optionCode)
                    ->atPath($optionCode)
                    ->addViolation();
            }
        }
    }
}
