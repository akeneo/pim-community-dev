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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Identifier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\DataMappingUuid;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operations;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Sources;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class IdentifierValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Identifier) {
            throw new UnexpectedTypeException($constraint, Identifier::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'uuid' => new DataMappingUuid(),
                'target' => new AttributeTarget([
                    'source_configuration' => new IsNull(),
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
