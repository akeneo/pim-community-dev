<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator as BaseFileValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validate files linked to product (need to validate extension and size).
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileValidator extends ConstraintValidator
{
    /** @var array */
    private $extensionToMimeTypeMapping;

    protected static $suffices = [
        1                            => 'bytes',
        BaseFileValidator::KB_BYTES  => 'kB',
        BaseFileValidator::MB_BYTES  => 'MB',
        BaseFileValidator::KIB_BYTES => 'KiB',
        BaseFileValidator::MIB_BYTES => 'MiB',
    ];

    /**
     * @param array $extensionToMimeTypeMapping
     */
    public function __construct(array $extensionToMimeTypeMapping)
    {
        $this->extensionToMimeTypeMapping = $extensionToMimeTypeMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof File) {
            throw new UnexpectedTypeException($constraint, File::class);
        }

        if ($value instanceof FileInfoInterface && (null !== $value->getId() || null !== $value->getUploadedFile())) {
            $this->validateFileSize($value, $constraint);
            $this->validateFileExtension($value, $constraint);
            $this->validateMimeType($value, $constraint);
        }
    }

    /**
     * Validate if extension is allowed.
     *
     * @param FileInfoInterface $fileInfo   The file that should be validated
     * @param File              $constraint The constraint for the validation
     */
    protected function validateFileExtension(FileInfoInterface $fileInfo, File $constraint)
    {
        if (empty($constraint->allowedExtensions)) {
            return;
        }

        if (!in_array($this->getExtension($fileInfo), $constraint->allowedExtensions)) {
            $this->context->buildViolation(
                $constraint->extensionsMessage,
                ['%extensions%' => implode(', ', $constraint->allowedExtensions)]
            )->addViolation();
        }
    }

    /**
     * Validate if file size is allowed.
     *
     * @param FileInfoInterface $fileInfo
     * @param File              $constraint
     */
    protected function validateFileSize(FileInfoInterface $fileInfo, File $constraint)
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
            $coef = BaseFileValidator::MIB_BYTES;
            $coefFactor = BaseFileValidator::KIB_BYTES;
        } else {
            $coef = BaseFileValidator::MB_BYTES;
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
            $sizeAsString = (string)round($size / $coef, 2);
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

    /**
     * @param FileInfoInterface $fileInfo
     * @param File              $constraint
     */
    private function validateMimeType(FileInfoInterface $fileInfo, File $constraint)
    {
        if (empty($constraint->allowedExtensions)) {
            return;
        }

        if (!array_key_exists($this->getExtension($fileInfo), $this->extensionToMimeTypeMapping)) {
            return;
        }

        $mappedMimeTypes = $this->extensionToMimeTypeMapping[$this->getExtension($fileInfo)];

        $mimeType = null !== $fileInfo->getUploadedFile() ?
            $fileInfo->getUploadedFile()->getMimeType() :
            $fileInfo->getMimeType();

        if (null !== $mimeType && !in_array($mimeType, $mappedMimeTypes)) {
            $this->context->buildViolation(
                $constraint->mimeTypeMessage,
                [
                    '%extension%' => $this->getExtension($fileInfo),
                    '%types%' => implode(', ', $mappedMimeTypes),
                    '%type%' => $mimeType
                ]
            )->addViolation();
        }
    }

    private function getExtension(FileInfoInterface $fileInfo): string
    {
        return null !== $fileInfo->getUploadedFile() ?
            strtolower($fileInfo->getUploadedFile()->getClientOriginalExtension()) :
            strtolower($fileInfo->getExtension());
    }
}
