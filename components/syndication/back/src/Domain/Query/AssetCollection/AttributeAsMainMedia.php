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

namespace Akeneo\Platform\Syndication\Domain\Query\AssetCollection;

abstract class AttributeAsMainMedia
{
    private bool $isScopable;
    private bool $isLocalizable;

    public function __construct(bool $isScopable, bool $isLocalizable)
    {
        $this->isScopable = $isScopable;
        $this->isLocalizable = $isLocalizable;
    }

    public function isScopable(): bool
    {
        return $this->isScopable;
    }

    public function isLocalizable(): bool
    {
        return $this->isLocalizable;
    }
}
