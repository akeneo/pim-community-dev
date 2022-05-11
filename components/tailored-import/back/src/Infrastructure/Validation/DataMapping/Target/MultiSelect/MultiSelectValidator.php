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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\MultiSelect;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\DataMappingUuid;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operations;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Sources;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MultiSelectValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MultiSelect) {
            throw new UnexpectedTypeException($constraint, MultiSelect::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'uuid' => new DataMappingUuid(),
                'target' => new AttributeTarget([
                    'source_configuration' => new IsNull(),
                    'action_if_not_empty' => new Choice([
                        TargetInterface::ACTION_ADD,
                        TargetInterface::ACTION_SET,
                    ]),
                    'action_if_empty' => new Choice([
                        TargetInterface::IF_EMPTY_CLEAR,
                        TargetInterface::IF_EMPTY_SKIP,
                    ]),
                ]),
                'sources' => new Sources(true, $constraint->getColumnUuids()),
                'operations' => new Operations([
                    SplitOperation::TYPE,
                ]),
                'sample_data' => new SampleData(),
            ],
        ]));
    }
}
