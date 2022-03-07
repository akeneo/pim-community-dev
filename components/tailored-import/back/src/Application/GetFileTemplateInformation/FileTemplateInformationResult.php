<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation;

use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileTemplateInformationResult
{
    private function __construct(
        private array $sheetNames,
        private array $headerCells,
    ) {
        Assert::notEmpty($this->sheetNames);
    }

    public static function create(array $sheetNames, array $columns): self
    {
        return new self($sheetNames, $columns);
    }

    public function normalize(): array
    {
        return [
          'sheets' => $this->sheetNames,
          'header_cells' => $this->headerCells,
        ];
    }
}
