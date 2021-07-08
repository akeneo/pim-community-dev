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

namespace Akeneo\Platform\TailoredExport\Application\Query\Column;

use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceCollection;

class Column
{
    private string $target;
    private SourceCollection $sourceCollection;

    public function __construct(
        string $target,
        SourceCollection $sourceCollection
    ) {
        $this->target = $target;
        $this->sourceCollection = $sourceCollection;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getSourceCollection(): SourceCollection
    {
        return $this->sourceCollection;
    }
}
