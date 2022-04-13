<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GeneratePreviewDataResult
{
    private function __construct(
        private array $previewData,
    ) {
    }

    public static function create(array $previewData): self
    {
        return new self($previewData);
    }

    public function normalize(): array
    {
        return [
            'preview_data' => $this->previewData
        ];
    }
}
