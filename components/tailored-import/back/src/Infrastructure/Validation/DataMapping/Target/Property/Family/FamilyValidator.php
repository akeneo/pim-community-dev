<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Property\Family;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\DataMappingUuid;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operations;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\PropertyTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Sources;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FamilyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Family) {
            throw new UnexpectedTypeException($constraint, Family::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'uuid' => new DataMappingUuid(),
                'target' => new PropertyTarget([
                    'action_if_not_empty' => new EqualTo(TargetInterface::ACTION_SET),
                    'action_if_empty' => new EqualTo(TargetInterface::IF_EMPTY_SKIP),
                ]),
                'sources' => new Sources(false, $constraint->getColumnUuids()),
                'operations' => new Operations([]),
                'sample_data' => new SampleData(),
            ],
        ]));
    }
}
