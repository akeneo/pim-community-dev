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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ImportStructureValidator extends ConstraintValidator
{
    public function validate($importStructure, Constraint $constraint): void
    {
        if (!$constraint instanceof ImportStructure) {
            throw new UnexpectedTypeException($constraint, ImportStructure::class);
        }

        $columnsUuid = array_map(static fn (array $column) => $column['uuid'], $importStructure['columns'] ?? []);
        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($importStructure, new Collection([
            'fields' => [
                'columns' => new Columns(),
                'data_mappings' => new DataMappings($columnsUuid),
            ],
        ]));
    }
}
