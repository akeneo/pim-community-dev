<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransformationCanNotHaveSameOperationTwiceValidator extends ConstraintValidator
{
    public function validate($operations, Constraint $constraint)
    {
        if (!$constraint instanceof TransformationCanNotHaveSameOperationTwice) {
            throw new UnexpectedTypeException($constraint, TransformationCanNotHaveSameOperationTwice::class);
        }

        if (!is_array($operations)) {
            throw new \InvalidArgumentException('operations must be an array.');
        }

        $definedOperationTypes = [];
        foreach ($operations as $operation) {
            if (in_array($operation['type'], $definedOperationTypes)) {
                $this->context->buildViolation(
                    TransformationCanNotHaveSameOperationTwice::ERROR_MESSAGE,
                    [
                        '%asset_family_identifier%' => $constraint->getAssetFamilyIdentifier()->__toString(),
                        '%operation_type%' => $operation['type'],
                    ]
                )->addViolation();
            }

            $definedOperationTypes[] = $operation['type'];
        }
    }
}
