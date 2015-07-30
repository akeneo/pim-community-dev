<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Validate files linked to product (need to validate extension and size).
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof FileInterface && (null !== $value->getId() || null !== $value->getUploadedFile())) {
            $this->validateFileSize($value, $constraint);
            $this->validateFileExtension($value, $constraint);
        }
    }

    /**
     * Validate if extension is allowed.
     *
     * @param FileInterface $file      The file that should be validated
     * @param Constraint    $constraint The constraint for the validation
     */
    protected function validateFileExtension(FileInterface $file, Constraint $constraint)
    {
        if (!empty($constraint->allowedExtensions)) {
            $extension = null !== $file->getUploadedFile() ?
                $file->getUploadedFile()->getClientOriginalExtension() :
                $file->getExtension()
            ;

            if (!in_array(strtolower($extension), $constraint->allowedExtensions)) {
                $this->context->addViolation(
                    $constraint->extensionsMessage,
                    ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
                );
            }
        }
    }

    /**
     * Validate if file size is allowed.
     *
     * @param FileInterface $file
     * @param Constraint    $constraint
     */
    protected function validateFileSize(FileInterface $file, Constraint $constraint)
    {
        // comes from Symfony\Component\Validator\Constraints\FileValidator
        if ($constraint->maxSize) {
            $fileSize = null !== $file->getUploadedFile() ?
                $file->getUploadedFile()->getSize() :
                $file->getSize()
            ;

            if (ctype_digit((string) $constraint->maxSize)) {
                $size = $fileSize;
                $limit = (int) $constraint->maxSize;
                $suffix = 'bytes';
            } elseif (preg_match('/^\d++k$/', $constraint->maxSize)) {
                $size = round($fileSize / 1024, 2);
                $limit = (int) $constraint->maxSize;
                $suffix = 'kB';
            } elseif (preg_match('/^\d++M$/', $constraint->maxSize)) {
                $size = round($fileSize / (1024 * 1024), 2);
                $limit = (int) $constraint->maxSize;
                $suffix = 'MB';
            } else {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid maximum size', $constraint->maxSize));
            }

            if ($size > $limit) {
                $this->context->addViolation($constraint->maxSizeMessage, [
                    '{{ size }}' => $size,
                    '{{ limit }}' => $limit,
                    '{{ suffix }}' => $suffix,
                    '{{ file }}' => $this->formatValue($file->getOriginalFilename()),
                ]);
            }
        }
    }
}
