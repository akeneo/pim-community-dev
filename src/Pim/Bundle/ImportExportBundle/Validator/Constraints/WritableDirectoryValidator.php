<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for writable directory constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WritableDirectoryValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!is_string($value) || strpos(dirname($value), DIRECTORY_SEPARATOR) !== 0) {
            $this->context->buildViolation($constraint->invalidMessage)
                ->addViolation();
        } else {
            $path = dirname($value);
            $writable = null;

            while (null === $writable && strlen($path) > 0) {
                if (is_dir($path)) {
                    $writable = is_writable($path);
                } else {
                    $path = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));
                }
            }

            if (true !== $writable) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
