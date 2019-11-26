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

class ThereShouldBeLessTransformationThanLimitValidator extends ConstraintValidator
{
    /** @var int */
    private $maxTransformationByAssetFamilyLimit;

    public function __construct(int $maxTransformationByAssetFamilyLimit)
    {
        $this->maxTransformationByAssetFamilyLimit = $maxTransformationByAssetFamilyLimit;
    }

    public function validate($transformations, Constraint $constraint)
    {
        if (!$constraint instanceof ThereShouldBeLessTransformationThanLimit) {
            throw new UnexpectedTypeException($constraint, ThereShouldBeLessTransformationThanLimit::class);
        }

        if (!is_array($transformations)) {
            throw new \InvalidArgumentException('transformations must be an array.');
        }

        $total = count($transformations);

        if ($total > $this->maxTransformationByAssetFamilyLimit) {
            $this->context->buildViolation(
                ThereShouldBeLessTransformationThanLimit::ERROR_MESSAGE,
                [
                    '%asset_family_identifier%' => $constraint->getAssetFamilyIdentifier()->__toString(),
                    '%limit%' => $this->maxTransformationByAssetFamilyLimit,
                ]
            )->addViolation();
        }
    }
}
