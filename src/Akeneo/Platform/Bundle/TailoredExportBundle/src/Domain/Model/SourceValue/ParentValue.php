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

namespace Akeneo\Platform\TailoredExport\Domain\Model\SourceValue;

class ParentValue implements SourceValueInterface
{
    private string $parentCode;

    public function __construct(string $parentCode)
    {
        $this->parentCode = $parentCode;
    }

    public function getParentCode(): string
    {
        return $this->parentCode;
    }
}
