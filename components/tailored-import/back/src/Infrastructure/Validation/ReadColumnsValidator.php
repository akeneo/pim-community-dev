<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReadColumnsValidator extends ConstraintValidator
{
    private const MAX_COLUMN_COUNT = 500;

    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ReadColumns) {
            throw new UnexpectedTypeException($constraint, ReadColumns::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $requiredParams = [
            'file_key',
            'file_structure',
        ];

        $missingParams = array_filter($requiredParams, fn ($param) => null === $value->get($param));

        if (count($missingParams) > 0) {
            $this->context->buildViolation(
                ReadColumns::MISSING_QUERY_PARAMS,
                [
                    '{{ missing_params }}' => implode(', ', $missingParams),
                ],
            )->addViolation();

            return;
        }

        $requestParams = [
            'file_key' => $value->get('file_key'),
            'file_structure' => $value->get('file_structure'),
        ];

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->atPath('[params]')->validate($requestParams, new Collection([
            'fields' => [
                'file_key' => [
                    new FileKey(),
                ],
                'file_structure' => [
                    new IsValidFileStructure(),
                ],
            ],
        ]));

        if ($validator->getViolations()->count() > 0) {
            return;
        }

        $fileStructure = $value->get('file_structure');
        $reader = $this->xlsxFileReaderFactory->create($value->get('file_key'));
        $headerRow = current($reader->readRows(
            $fileStructure['sheet_name'],
            $fileStructure['header_row'],
            1,
        ));

        $this->validateLessThan500Column($headerRow);
        $this->validateNoEmptyHeader($headerRow);
    }

    private function validateLessThan500Column(
        array $columns,
    ): void {
        if (count($columns) > 500) {
            $this->context->buildViolation(ReadColumns::MAX_COUNT_REACHED)
                ->setParameter('limit', strval(self::MAX_COLUMN_COUNT))
                ->addViolation();
        }
    }

    private function validateNoEmptyHeader(array $headerRow): void
    {
        foreach ($headerRow as $cell) {
            if (empty($cell)) {
                $this->context->buildViolation(ReadColumns::EMPTY_HEADER)->addViolation();

                return;
            }
        }
    }
}
