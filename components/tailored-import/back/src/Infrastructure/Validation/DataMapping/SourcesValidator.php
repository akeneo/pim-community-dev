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

class SourcesValidator extends ConstraintValidator
{
    private const SOURCES_MIN_COUNT = 1;
    private const MONO_SOURCES_MAX_COUNT = 1;
    private const MULTI_SOURCES_MAX_COUNT = 4;

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Sources) {
            throw new UnexpectedTypeException($constraint, Sources::class);
        }

        $this->context->getValidator()->validate($value, [
            new Type('array'),
            new Unique([
                'message' => Sources::SOURCES_SHOULD_BE_UNIQUE,
            ]),
        ]);

        $this->validateSourcesCount($value, $constraint);
        $this->validateSourcesExist($value, $constraint);
    }

    private function validateSourcesCount(array $dataMapping, Sources $constraint): void
    {
        $maxSourcesCount = $constraint->supportsMultiSource() ? self::MONO_SOURCES_MAX_COUNT : self::MULTI_SOURCES_MAX_COUNT;

        $this->context->getValidator()
            ->inContext($this->context)
            ->atPath(sprintf('[%s][sources]', $dataMapping['uuid'] ?? 'null'))
            ->validate($dataMapping['sources'], new Count([
                'min' => self::SOURCES_MIN_COUNT,
                'minMessage' => Sources::MIN_SOURCES_COUNT_REACHED,
                'max' => $maxSourcesCount,
                'maxMessage' => Sources::MAX_SOURCES_COUNT_REACHED,
                'exactMessage' => Sources::SOURCES_COUNT_MISMATCHED,
            ]));
    }

    private function validateSourcesExist(array $dataMapping, Sources $constraint): void
    {
        $columns = $constraint->getColumns();
        $columnsUuid = array_map(static fn (array $column) => $column['uuid'], $columns);

        foreach ($dataMapping['sources'] as $source) {
            if (!in_array($source, $columnsUuid)) {
                $this->context->buildViolation(
                    Sources::SOURCES_SHOULD_EXIST,
                )
                    ->atPath(sprintf('[%s][sources]', $dataMapping['uuid']))
                    ->addViolation();
            }
        }
    }

}
