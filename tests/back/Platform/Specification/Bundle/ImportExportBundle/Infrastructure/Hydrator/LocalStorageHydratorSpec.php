<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

class LocalStorageHydratorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith();
    }

    public function it_supports_only_local_storage()
    {
        $this->supports(['type' => 'local', 'file_path' => 'a_file_path'])->shouldReturn(true);
        $this->supports(['type' => 'none'])->shouldReturn(false);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
    }

    public function it_hydrates_a_local_storage()
    {
        $this->hydrate(['type' => 'local', 'file_path' => 'a_file_path'])->shouldBeLike(new LocalStorage('a_file_path'));
    }
}
