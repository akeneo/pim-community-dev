<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
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
        $this->hydrate(['type' => 'none'])->shouldBeLike(new NoneStorage());
    }
}
