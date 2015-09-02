<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator as BaseFileValidator;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate files linked to product (need to validate extension and size).
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileValidator extends ConstraintValidator
{
    protected static $suffices = [
        1                            => 'bytes',
        BaseFileValidator::KB_BYTES  => 'kB',
        BaseFileValidator::MB_BYTES  => 'MB',
        BaseFileValidator::KIB_BYTES => 'KiB',
        BaseFileValidator::MIB_BYTES => 'MiB',
    ];

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof FileInfoInterface && (null !== $value->getId() || null !== $value->getUploadedFile())) {
            $this->validateFileSize($value, $constraint);
            $this->validateFileExtension($value, $constraint);
        }
    }

    /**
     * Validate if extension is allowed.
     *
     * @param FileInfoInterface $fileInfo   The file that should be validated
     * @param Constraint        $constraint The constraint for the validation
     */
    protected function validateFileExtension(FileInfoInterface $fileInfo, Constraint $constraint)
    {
        if (!empty($constraint->allowedExtensions)) {
            $extension = null !== $fileInfo->getUploadedFile() ?
                $fileInfo->getUploadedFile()->getClientOriginalExtension() :
                $fileInfo->getExtension();

            if (!in_array(strtolower($extension), $constraint->allowedExtensions)) {
                $this->context->buildViolation(
                    $constraint->extensionsMessage,
                    ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
                )->addViolation();
            }
        }
    }

    /**
     * Validate if file size is allowed.
     *
     * @param FileInfoInterface $fileInfo
     * @param Constraint        $constraint
     */
    protected function validateFileSize(FileInfoInterface $fileInfo, Constraint $constraint)
    {
        // comes from Symfony\Component\Validator\Constraints\FileValidator
        if ($constraint->maxSize) {
            $limitInBytes = $constraint->maxSize;

            if ($fileInfo->getSize() > $limitInBytes) {
                $factorizeSizes = $this->factorizeSizes($fileInfo->getSize(), $limitInBytes, $constraint->binaryFormat);
                list($sizeAsString, $limitAsString, $suffix) = $factorizeSizes;

                $this->context->buildViolation($constraint->maxSizeMessage)
                    ->setParameter('{{ file }}', $this->formatValue($fileInfo->getOriginalFilename()))
                    ->setParameter('{{ size }}', $sizeAsString)
                    ->setParameter('{{ limit }}', $limitAsString)
                    ->setParameter('{{ suffix }}', $suffix)
                    ->setCode(File::TOO_LARGE_ERROR)
                    ->addViolation();

                return;
            }
        }
    }

    /**
     * Convert the limit to the smallest possible number
     * (i.e. try "MB", then "kB", then "bytes")
     */
    protected function factorizeSizes($size, $limit, $binaryFormat)
    {
        if ($binaryFormat) {
            $coef       = BaseFileValidator::MIB_BYTES;
            $coefFactor = BaseFileValidator::KIB_BYTES;
        } else {
            $coef       = BaseFileValidator::MB_BYTES;
            $coefFactor = BaseFileValidator::KB_BYTES;
        }

        $limitAsString = (string)($limit / $coef);

        // Restrict the limit to 2 decimals (without rounding! we
        // need the precise value)
        while (self::moreDecimalsThan($limitAsString, 2)) {
            $coef /= $coefFactor;
            $limitAsString = (string)($limit / $coef);
        }

        // Convert size to the same measure, but round to 2 decimals
        $sizeAsString = (string)round($size / $coef, 2);

        // If the size and limit produce the same string output
        // (due to rounding), reduce the coefficient
        while ($sizeAsString === $limitAsString) {
            $coef /= $coefFactor;
            $limitAsString = (string)($limit / $coef);
            $sizeAsString  = (string)round($size / $coef, 2);
        }

        return [$sizeAsString, $limitAsString, self::$suffices[$coef]];
    }

    /**
     * @param double $double
     * @param int    $numberOfDecimals
     *
     * @return bool
     */
    protected static function moreDecimalsThan($double, $numberOfDecimals)
    {
        return strlen((string)$double) > strlen(round($double, $numberOfDecimals));
    }
}
