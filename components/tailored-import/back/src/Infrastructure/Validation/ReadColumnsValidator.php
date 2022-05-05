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

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
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

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->validate($value->request->all(), new Collection([
            'fields' => [
                'file_key' => new FileKey(),
                'file_structure' => new IsValidFileStructure(),
            ],
        ]));

        if ($validator->getViolations()->count() > 0) {
            return;
        }

        $fileStructure = $value->get('file_structure');
        $reader = $this->xlsxFileReaderFactory->create($value->get('file_key'));
        $headerRow = $reader->readRow($fileStructure['sheet_name'], $fileStructure['header_row']);
        $headerRow = $this->truncateHeaderToFirstColumn($headerRow, $fileStructure['first_column']);

        $this->validateLessThan500Column($headerRow);
        $this->validateNoEmptyHeader($headerRow);
    }

    private function validateLessThan500Column(
        array $columns,
    ): void {
        $this->context->getValidator()->inContext($this->context)->validate($columns, new Count(
            [
                'min' => 1,
                'max' => self::MAX_COLUMN_COUNT,
                'minMessage' => ReadColumns::AT_LEAST_ONE_COLUMN,
                'maxMessage' => ReadColumns::MAX_COUNT_REACHED,
            ],
        ));
    }

    private function validateNoEmptyHeader(array $headerRow): void
    {
        foreach ($headerRow as $cell) {
            if (empty($cell)) {
                $this->context->addViolation(ReadColumns::EMPTY_HEADER);

                return;
            }
        }
    }

    private function truncateHeaderToFirstColumn(array $headerRow, int $firstColumn): array
    {
        return array_slice($headerRow, $firstColumn);
    }
}
