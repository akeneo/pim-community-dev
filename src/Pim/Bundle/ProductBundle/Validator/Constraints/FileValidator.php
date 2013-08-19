<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\FileValidator as BaseFileValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileValidator extends BaseFileValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);

        if (null === $value || '' === $value) {
            return;
        }

        if ($constraint->allowedExtensions) {
            $file = !$value instanceof \SplFileInfo ? new \SplFileInfo($value) : $value;
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (!in_array($extension, $constraint->allowedExtensions)) {
                $this->context->addViolation(
                    $constraint->extensionsMessage,
                    array('{{ extensions }}' => join(', ', $constraint->allowedExtensions))
                );
            }
        }
    }
}
