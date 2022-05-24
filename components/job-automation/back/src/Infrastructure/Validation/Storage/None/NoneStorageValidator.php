<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\None;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage as NoneStorageModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoneStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoneStorage) {
            throw new UnexpectedTypeException($constraint, NoneStorage::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(NoneStorageModel::TYPE),
            ],
        ]));
    }
}
