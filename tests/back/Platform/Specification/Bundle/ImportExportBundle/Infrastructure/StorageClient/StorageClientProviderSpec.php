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

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

class StorageClientProviderSpec
{
    public function let(
        FilesystemProvider $filesystemProvider,
        StorageClientProviderInterface $firstClientProvider,
        StorageClientProviderInterface $secondClientProvider,
    ) {
        $this->beConstructedWith($filesystemProvider, [$firstClientProvider, $secondClientProvider]);
    }

    public function it_return_storage_client_from_file_to_transfer(FileToTransfer $fileToTransfer)
    {
        $this->getFromFileToTransfer($fileToTransfer)->shouldReturnAnInstanceOf(StorageClientInterface::class);
    }

    public function it_returns_first_client_provider_that_support_storage_configuration(
        StorageInterface $storage,
        StorageClientProviderInterface $firstClientProvider,
        StorageClientProviderInterface $secondClientProvider,
        StorageClientProviderInterface $thirdClientProvider,
        StorageClientInterface $storageClient,
    ) {
        $firstClientProvider->supports($storage)->willReturn(false);
        $secondClientProvider->supports($storage)->willReturn(true);
        $thirdClientProvider->supports($storage)->willReturn(false);

        $secondClientProvider->getFromStorage($storage)->shouldBeCalledOnce()->willReturn($storageClient);

        $this->getFromStorage($storage)->willReturn($storageClient);
    }

    public function it_throws_an_exception_when_no_client_provider_support_the_storage_configuration(
        StorageInterface $storage,
        StorageClientProviderInterface $firstClientProvider,
        StorageClientProviderInterface $secondClientProvider,
        StorageClientProviderInterface $thirdClientProvider,
    ) {
        $firstClientProvider->supports($storage)->willReturn(false);
        $secondClientProvider->supports($storage)->willReturn(false);
        $thirdClientProvider->supports($storage)->willReturn(false);

        $this->shouldThrow(\RuntimeException::class)
            ->during('getFromStorage', [$storage]);
    }
}
