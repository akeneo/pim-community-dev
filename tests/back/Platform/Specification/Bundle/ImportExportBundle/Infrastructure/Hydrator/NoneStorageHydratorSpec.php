<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use PhpSpec\ObjectBehavior;

class NoneStorageHydratorSpec extends ObjectBehavior
{
    public function it_supports_only_none_storage()
    {
        $this->supports(['type' => 'none'])->shouldReturn(true);
        $this->supports(['type' => 'local'])->shouldReturn(false);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
    }

    public function it_returns_null()
    {
        $this->hydrate(['type' => 'none'])->shouldReturn(null);
    }
}
