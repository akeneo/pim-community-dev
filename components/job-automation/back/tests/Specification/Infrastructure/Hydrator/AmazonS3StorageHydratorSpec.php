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

use Akeneo\Platform\JobAutomation\Domain\Model\Storage\AmazonS3Storage;
use PhpSpec\ObjectBehavior;

class AmazonS3StorageHydratorSpec extends ObjectBehavior
{
    public function it_supports_only_amazon_s3_storage()
    {
        $this->supports([
            'type' => 'amazon_s3',
            'region' => 'eu-west-3',
            'bucket' => 'a_bucket',
            'key' => 'a_key',
            'secret' => 'a_secret',
            'file_path' => 'upload',
        ])->shouldReturn(true);
        $this->supports(['type' => 'none'])->shouldReturn(false);
        $this->supports(['type' => 'local'])->shouldReturn(false);
        $this->supports(['type' => 'sftp'])->shouldReturn(false);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
    }

    public function it_hydrates_an_amazon_s3_storage()
    {
        $this->hydrate([
            'type' => 'amazon_s3',
            'region' => 'eu-west-3',
            'bucket' => 'a_bucket',
            'key' => 'a_key',
            'secret' => 'a_secret',
            'file_path' => 'upload',
        ])->shouldBeLike(
            new AmazonS3Storage(
                'eu-west-3',
                'a_bucket',
                'a_key',
                'a_secret',
                'upload'
            ),
        );
    }
}
