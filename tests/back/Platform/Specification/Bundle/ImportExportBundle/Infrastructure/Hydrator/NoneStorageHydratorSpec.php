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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use PhpSpec\ObjectBehavior;

class NoneStorageHydratorSpec extends ObjectBehavior
{
    public function it_supports_only_local_storage()
    {
        $this->supports(['type' => 'none'])->shouldReturn(true);
        $this->supports(['type' => 'local'])->shouldReturn(false);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
    }

    public function it_hydrates_a_none_storage()
    {
        $this->hydrate(['type' => 'local', 'file_path' => 'a_file_path'])->shouldBeLike(new NoneStorage());
    }
}
