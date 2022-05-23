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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

class LocalStorageHydratorSpec extends ObjectBehavior
{
    public function let(VersionProviderInterface $versionProvider)
    {
        $versionProvider->isSaaSVersion()->willReturn(false);
        $this->beConstructedWith($versionProvider);
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

    public function it_throws_an_exception_on_saas_environment(VersionProviderInterface $versionProvider)
    {
        $versionProvider->isSaaSVersion()->willReturn(true);

        $this->shouldThrow(\InvalidArgumentException::class)->during('hydrate', [['type' => 'local', 'file_path' => 'a_file_path']]);
    }
}
