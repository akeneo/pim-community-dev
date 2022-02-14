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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UploadedFlatFileValidator extends ConstraintValidator
{
    public function validate($uploadedFlatFile, Constraint $constraint)
    {
        if (!$constraint instanceof UploadedFlatFile) {
            throw new UnexpectedTypeException($constraint, ImportStructure::class);
        }

        //TODO RAB-567: define rules for file (size limit, extension, ...)

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($uploadedFlatFile, [
            new Valid(),
            new File(),
        ]);
    }
}
