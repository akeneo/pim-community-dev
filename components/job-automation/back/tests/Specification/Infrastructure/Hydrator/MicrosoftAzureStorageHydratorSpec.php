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

use Akeneo\Platform\JobAutomation\Domain\Model\Storage\MicrosoftAzureStorage;
use PhpSpec\ObjectBehavior;

class MicrosoftAzureStorageHydratorSpec extends ObjectBehavior
{
    public function it_supports_only_microsoft_azure_storage()
    {
        $this->supports([
            'type' => 'microsoft_azure',
            'connection_string' => 'a_connection_string',
            'container_name' => 'a_container_name',
            'file_path' => '/tmp/products.xlsx',
        ])->shouldReturn(true);
        $this->supports(['type' => 'none'])->shouldReturn(false);
        $this->supports(['type' => 'local'])->shouldReturn(false);
        $this->supports(['type' => 'sftp'])->shouldReturn(false);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
        $this->supports(['type' => 'amazon_s3'])->shouldReturn(false);
    }

    public function it_hydrates_a_microsoft_azure_storage()
    {
        $this->hydrate([
            'type' => 'microsoft_azure',
            'connection_string' => 'a_connection_string',
            'container_name' => 'a_container_name',
            'file_path' => '/tmp/products.xlsx',
        ])->shouldBeLike(
            new MicrosoftAzureStorage(
                'a_connection_string',
                'a_container_name',
                '/tmp/products.xlsx',
            ),
        );
    }
}
