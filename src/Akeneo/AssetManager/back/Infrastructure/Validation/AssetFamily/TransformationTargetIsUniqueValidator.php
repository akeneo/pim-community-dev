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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationTargetIsUniqueValidator extends ConstraintValidator
{
    public function validate($normalizedTransformations, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);

        if (!is_array($normalizedTransformations)) {
            return;
        }

        // Remove bad target formatting
        foreach ($normalizedTransformations as $index => $transformation) {
            if (!is_array($transformation) || !$this->hasValidTarget($transformation)) {
                unset($normalizedTransformations[$index]);
            }
        }

        $normalizedTransformations = array_values($normalizedTransformations);
        foreach ($normalizedTransformations as $index => $transformation) {
            $target = $transformation['target'];
            $otherTransformations = array_slice($normalizedTransformations, $index + 1);

            foreach ($otherTransformations as $otherTransformation) {
                $otherTarget = $otherTransformation['target'];
                if ($target['attribute'] === $otherTarget['attribute']
                    && $target['channel'] === $otherTarget['channel']
                    && $target['locale'] === $otherTarget['locale']
                ) {
                    $this->context->buildViolation(TransformationTargetIsUnique::ERROR_MESSAGE)->addViolation();

                    return;
                }
            }
        }
    }

    protected function hasValidTarget(array $transformation): ?bool
    {
        $target = $transformation['target'] ?? [];

        return array_key_exists('attribute', $target)
            && array_key_exists('channel', $target)
            && array_key_exists('locale', $target)
        ;
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof TransformationTargetIsUnique) {
            throw new UnexpectedTypeException($constraint, TransformationTargetIsUnique::class);
        }
    }
}
