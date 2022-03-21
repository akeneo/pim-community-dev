<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileTemplateInformationResult
{
    private function __construct(
        private array $sheetNames,
        private array $rows,
    ) {
    }

    public static function create(array $sheetNames, array $rows): self
    {
        return new self($sheetNames, $rows);
    }

    public function normalize(): array
    {
        return [
            'sheet_names' => $this->sheetNames,
            'rows' => $this->rows,
            'column_count' => empty($this->rows) ? 0 : count($this->rows[0]),
        ];
    }
}
