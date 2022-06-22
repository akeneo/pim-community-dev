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

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

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
            'preview_data' => array_map(
                static fn (array $operationValue) => array_map(
                    static fn (ValueInterface $value) => $value->getValue(),
                    $operationValue,
                ),
                $this->previewData,
            ),
        ];
    }
}
