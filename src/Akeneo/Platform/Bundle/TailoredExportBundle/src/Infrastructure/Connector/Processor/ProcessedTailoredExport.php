<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractedMedia;

class ProcessedTailoredExport
{
    private array $itemsToWrite;

    /** @var ExtractedMedia[] */
    private array $mediaToExport;

    public function __construct(array $itemsToWrite, array $mediaToExport)
    {
        $this->itemsToWrite = $itemsToWrite;
        $this->mediaToExport = $mediaToExport;
    }

    public function getItems(): array
    {
        return $this->itemsToWrite;
    }

    public function getExtractedMediaCollection(): array
    {
        return $this->mediaToExport;
    }
}
