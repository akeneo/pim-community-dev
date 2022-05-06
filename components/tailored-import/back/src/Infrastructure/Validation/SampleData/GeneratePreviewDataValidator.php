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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operations;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GeneratePreviewDataValidator extends ConstraintValidator
{
    /** @var string[] */
    private array $operationTypes;

    public function __construct(private array $operationConstraints)
    {
        $this->operationTypes = array_keys($this->operationConstraints);
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof GeneratePreviewData) {
            throw new UnexpectedTypeException($constraint, GeneratePreviewData::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $this->context->getValidator()->inContext($this->context)->validate($value->request->all(), new Collection([
            'sample_data' => new SampleData(),
            'operations' => new Operations($this->operationTypes),
            'target' => new AttributeTarget(),
        ]));
    }
}
