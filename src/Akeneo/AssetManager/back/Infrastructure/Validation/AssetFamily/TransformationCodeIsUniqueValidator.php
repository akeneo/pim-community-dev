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

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationCodeIsUniqueValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        Assert::isInstanceOfAny($command, [CreateAssetFamilyCommand::class, EditAssetFamilyCommand::class]);

        if ($command->transformations === null) {
            return;
        }

        foreach (array_values($command->transformations) as $index => $transformation) {
            $subTransformations = array_slice($command->transformations, $index + 1);
            if ($this->thereIsATransformationWithSameCode($transformation, $subTransformations)) {
                $this->context->buildViolation(TransformationCodeIsUnique::ERROR_MESSAGE)
                    ->setParameter('%transformation_code%', $transformation['code'])
                    ->addViolation();
            }
        }
    }

    private function thereIsATransformationWithSameCode(array $transformation, array $transformations): bool
    {
        if (!isset($transformation['code'])) {
            return false;
        }

        foreach ($transformations as $otherTransformation) {
            if (!isset($otherTransformation['code'])) {
                continue;
            }

            if ($transformation['code'] === $otherTransformation['code']) {
                return true;
            }
        }

        return false;
    }

    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof TransformationCodeIsUnique) {
            throw new UnexpectedTypeException($constraint, TransformationCodeIsUnique::class);
        }
    }
}
