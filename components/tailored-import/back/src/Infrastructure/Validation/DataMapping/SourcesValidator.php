<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SourcesValidator extends ConstraintValidator
{
    private const SOURCES_MIN_COUNT = 1;
    private const SOURCES_MAX_COUNT = 4;

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Sources) {
            throw new UnexpectedTypeException($constraint, Sources::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, [
            new Type('array'),
            new Unique([
                'message' => Sources::SOURCES_SHOULD_BE_UNIQUE,
            ]),
        ]);

        $this->validateSourcesCount($value, $constraint);
        $this->validateSourcesExist($value, $constraint);
    }

    private function validateSourcesCount(array $sources, Sources $constraint): void
    {
        $constraint = $constraint->supportsMultiSource() ?
            new Count([
                'min' => self::SOURCES_MIN_COUNT,
                'minMessage' => Sources::AT_LEAST_ONE_REQUIRED,
                'max' => self::SOURCES_MAX_COUNT,
                'maxMessage' => Sources::MAX_SOURCES_COUNT_REACHED,
            ])
            : new Count([
                'min' => self::SOURCES_MIN_COUNT,
                'max' => self::SOURCES_MIN_COUNT,
                'exactMessage' => Sources::AT_LEAST_ONE_REQUIRED,
            ]);

        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($sources, $constraint);
    }

    private function validateSourcesExist(array $sources, Sources $constraint): void
    {
        $columns = $constraint->getColumnUuids();

        foreach ($sources as $index => $source) {
            if (!in_array($source, $columns)) {
                $this->context
                    ->buildViolation(Sources::SOURCES_SHOULD_EXIST)
                    ->atPath(sprintf('[%s]', $index))
                    ->addViolation();
            }
        }
    }
}
