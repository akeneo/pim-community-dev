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

namespace Akeneo\Platform\Syndication\Application\Common\Column;

use Akeneo\Platform\Syndication\Application\Common\Format\FormatInterface;
use Akeneo\Platform\Syndication\Application\Common\Source\SourceCollection;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;

class Column
{
    private Target $target;
    private SourceCollection $sourceCollection;
    private FormatInterface $format;

    public function __construct(
        Target $target,
        SourceCollection $sourceCollection,
        FormatInterface $format
    ) {
        $this->target = $target;
        $this->sourceCollection = $sourceCollection;
        $this->format = $format;
    }

    public function getTarget(): Target
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
