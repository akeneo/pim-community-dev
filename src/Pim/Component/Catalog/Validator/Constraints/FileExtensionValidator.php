<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate files extensions
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileExtensionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_string($value)) {
            $this->validateFileExtension($value, $constraint);
        }
    }

    /**
     * Validate if extension is allowed.
     *
     * @param string     $filePath   The path of the file that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    protected function validateFileExtension($filePath, Constraint $constraint)
    {
        if (!empty($constraint->allowedExtensions)) {
            $extensionTokens = explode('.', $filePath);
            $extension = end($extensionTokens);
            if (!in_array(strtolower($extension), $constraint->allowedExtensions)) {
                $this->context->buildViolation(
                    $constraint->extensionsMessage,
                    ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
                )->addViolation();
            }
        }
    }
}
