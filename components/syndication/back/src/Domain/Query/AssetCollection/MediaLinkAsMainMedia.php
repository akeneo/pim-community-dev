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

class MediaLinkAsMainMedia extends AttributeAsMainMedia
{
    private string $prefix;
    private string $suffix;

    public function __construct(bool $isScopable, bool $isLocalizable, string $prefix, string $suffix)
    {
        parent::__construct($isScopable, $isLocalizable);
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }
}
