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

use Webmozart\Assert\Assert;

class GroupsValue implements SourceValueInterface
{
    /** @var string[] */
    private array $groupCodes;

    public function __construct(array $groupCodes)
    {
        Assert::allString($groupCodes);

        $this->groupCodes = $groupCodes;
    }

    public function getGroupCodes(): array
    {
        return $this->groupCodes;
    }
}
