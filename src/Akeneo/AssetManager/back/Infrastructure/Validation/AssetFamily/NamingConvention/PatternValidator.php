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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Pattern as NamingConventionPattern;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PatternValidator extends ConstraintValidator
{
    public function validate($stringPattern, Constraint $constraint)
    {
        if (!$constraint instanceof Pattern) {
            throw new UnexpectedTypeException($constraint, Pattern::class);
        }

        try {
            NamingConventionPattern::create($stringPattern);
        } catch (\InvalidArgumentException $e) {
            $this->context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
