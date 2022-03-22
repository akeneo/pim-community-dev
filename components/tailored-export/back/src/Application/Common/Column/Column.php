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

namespace Akeneo\Platform\TailoredExport\Application\Common\Column;

use Akeneo\Platform\TailoredExport\Application\Common\Format\FormatInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;

class Column
{
    public function __construct(
        private string $target,
        private SourceCollection $sourceCollection,
        private FormatInterface $format,
    ) {
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getSourceCollection(): SourceCollection
    {
        return $this->sourceCollection;
    }

    public function getFormat(): FormatInterface
    {
        return $this->format;
    }
}
