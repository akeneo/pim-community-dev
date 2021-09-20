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

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Platform;

class AttributeAsMainMedia
{
    private string $type;
    private bool $isScopable;
    private bool $isLocalizable;

    public function __construct(string $type, bool $isScopable, bool $isLocalizable)
    {
        $this->type = $type;
        $this->isScopable = $isScopable;
        $this->isLocalizable = $isLocalizable;
    }

    public function isMediaFile(): bool
    {
        return $this->type === 'media_file';
    }

    public function isMediaLink(): bool
    {
        return $this->type === 'media_link';
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
