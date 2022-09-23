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

namespace Akeneo\Platform\Syndication\Application\Common\Operation\String;

use Akeneo\Platform\Syndication\Application\Common\Operation\OperationInterface;

class SplitOperation implements OperationInterface
{
    private string $separator;

    public function __construct(string $separator)
    {
        $this->separator = $separator;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }
}
