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

use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\AttributeTarget as AttributeTargetConstraint;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operations;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\PropertyTarget as PropertyTargetConstraint;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
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
            'fields' => [
                'target' => new Collection([
                    'fields' => [
                        'type' => new Choice([
                            AttributeTarget::TYPE,
                            PropertyTarget::TYPE,
                        ]),
                    ],
                    'allowExtraFields' => true,
                ]),
            ],
            'allowExtraFields' => true,
        ]));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $targetConstraintClass = $this->getTargetConstraintClass($value->get('target'));

        $this->context->getValidator()->inContext($this->context)->validate($value->request->all(), new Collection([
            'sample_data' => new SampleData(),
            'operations' => new Operations($this->operationTypes),
            'target' => new $targetConstraintClass(),
        ]));
    }

    private function getTargetConstraintClass(array $target): string
    {
        return match ($target['type']) {
            AttributeTarget::TYPE => AttributeTargetConstraint::class,
            PropertyTarget::TYPE => PropertyTargetConstraint::class,
            default => throw new \InvalidArgumentException(sprintf('Unknown target type "%s"', $target['type'])),
        };
    }
}
