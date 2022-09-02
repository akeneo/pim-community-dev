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

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Hydrator;

use Akeneo\Platform\JobAutomation\Domain\Model\SftpStorage;
use PhpSpec\ObjectBehavior;

class SftpStorageHydratorSpec extends ObjectBehavior
{
    public function it_supports_only_sftp_storage()
    {
        $this->supports([
            'type' => 'sftp',
            'host' => 'my_host',
            'username' => 'user',
            'password' => 'my_favorite_password',
            'file_path' => 'upload',
            'port' => 22,
        ])->shouldReturn(true);
        $this->supports(['type' => 'none'])->shouldReturn(false);
        $this->supports(['type' => 'local'])->shouldReturn(false);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
    }

    public function it_hydrates_a_sftp_storage()
    {
        $this->hydrate([
            'type' => 'sftp',
            'host' => 'my_host',
            'username' => 'user',
            'password' => 'my_favorite_password',
            'file_path' => 'upload',
            'port' => 22,
        ])->shouldBeLike(
            new SftpStorage('my_host', 22, 'user', 'my_favorite_password', 'upload'),
        );
    }
}
