<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\FileValidator as BaseFileValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Model\Media;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
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
        if ($value instanceof Media) {
            $value = $value->getFile();
        }

        if (null === $value || '' === $value) {
            return;
        }

        parent::validate($value, $constraint);

        $this->validateAllowedExtension($value, $constraint);
    }

    /**
     * Validate if extension is allowed
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    protected function validateAllowedExtension($value, Constraint $constraint)
    {
        if ($constraint->allowedExtensions) {
            $file = $value instanceof \SplFileInfo ? $value : new \SplFileInfo($value);

            if ($file instanceof UploadedFile) {
                $extension = $file->getClientOriginalExtension();
            } elseif ($file instanceof FileObject) {
                $extension = $file->getExtension();
            } else {
                $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            }

            if (!in_array(strtolower($extension), $constraint->allowedExtensions)) {
                $this->context->addViolation(
                    $constraint->extensionsMessage,
                    ['{{ extensions }}' => join(', ', $constraint->allowedExtensions)]
                );
            }
        }
    }
}
