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
    private const VALID_SHA512_FINGERPRINT = '6f:0a:fc:c7:59:32:0d:7f:78:1b:76:24:a9:51:a4:f9:c3:35:4b:7c:e6:0d:28:d4:cd:5e:5d:62:51:85:e4:93:60:f4:ae:70:a1:ac:ba:1c:92:c7:f4:4a:55:3b:7e:ac:c3:14:0f:4f:d2:b7:e7:87:d7:4f:e2:6d:1e:ab:0c:92';

    public function it_supports_only_sftp_storage()
    {
        $this->supports([
            'type' => 'sftp',
            'host' => 'my_host',
            'username' => 'user',
            'password' => 'my_favorite_password',
            'file_path' => 'upload',
            'port' => 22,
            'login_type' => 'password',
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
            'login_type' => 'password',
        ])->shouldBeLike(
            new SftpStorage('my_host', 22, 'password', 'user', 'my_favorite_password', 'upload'),
        );

        $this->hydrate([
            'type' => 'sftp',
            'host' => 'my_host',
            'fingerprint' => self::VALID_SHA512_FINGERPRINT,
            'username' => 'user',
            'password' => 'my_favorite_password',
            'file_path' => 'upload',
            'port' => 22,
        ])->shouldBeLike(
            new SftpStorage('my_host', 22, 'user', 'my_favorite_password', 'upload', self::VALID_SHA512_FINGERPRINT),
        );
    }
}
