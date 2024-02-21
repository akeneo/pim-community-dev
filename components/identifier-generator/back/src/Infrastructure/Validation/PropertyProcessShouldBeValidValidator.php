<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PropertyProcessShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate($process, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, PropertyProcessShouldBeValid::class);
        if (!\is_array($process)) {
            return;
        }

        if (!\array_key_exists('type', $process)) {
            return;
        }

        switch ($process['type']) {
            case Process::PROCESS_TYPE_NO:
                $this->validateProcessTypeNo($process);

                break;
            case Process::PROCESS_TYPE_TRUNCATE:
                $this->validateProcessTypeTruncate($process, $constraint);

                break;
            case Process::PROCESS_TYPE_NOMENCLATURE:
                $this->validateProcessTypeNomenclature($process);
        }
    }

    /**
     * @param array<string, mixed> $process
     */
    private function validateProcessTypeNo(array $process): void
    {
        $this->validator->inContext($this->context)->validate($process, new Collection([
            'fields' => [
                'type' => null,
            ],
        ]));
    }

    /**
     * @param array<string, mixed> $process
     */
    private function validateProcessTypeTruncate(array $process, PropertyProcessShouldBeValid $constraint): void
    {
        $this->validator->inContext($this->context)->validate($process, new Collection([
            'fields' => [
                'type' => null,
                'operator' => new Choice(
                    choices: [Process::PROCESS_OPERATOR_EQ, Process::PROCESS_OPERATOR_LTE],
                    message: $constraint->processUnknownOperator
                ),
                'value' => [
                    new Type([
                        'type' => 'integer',
                    ]),
                    new Range([
                        'min' => 1,
                        'max' => 5,
                    ]),
                ],
            ],
        ]));
    }

    /**
     * @param array<string, mixed> $process
     */
    private function validateProcessTypeNomenclature(array $process): void
    {
        $this->validator->inContext($this->context)->validate($process, new Collection([
            'fields' => [
                'type' => null,
            ],
        ]));
    }
}
