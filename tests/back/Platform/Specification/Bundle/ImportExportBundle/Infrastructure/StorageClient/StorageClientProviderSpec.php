<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;

class StorageClientProviderSpec extends ObjectBehavior
{
    public function let(
        FilesystemProvider $filesystemProvider,
        StorageClientProviderInterface $firstClientProvider,
        StorageClientProviderInterface $secondClientProvider,
    ) {
        $this->beConstructedWith($filesystemProvider, [$firstClientProvider, $secondClientProvider]);
    }

    public function it_returns_storage_client_from_file_to_transfer(
        FilesystemProvider $filesystemProvider,
        FilesystemOperator $filesystemOperator,
    ) {
        $filesystemProvider->getFilesystem('local')->willReturn($filesystemOperator);
        $fileToTransfer = new FileToTransfer('fileKey', 'local', 'outputFileName', false);
        $this->getFromFileToTransfer($fileToTransfer)->shouldReturnAnInstanceOf(StorageClientInterface::class);
    }

    public function it_returns_first_client_provider_that_support_storage_configuration(
        StorageClientProviderInterface $firstClientProvider,
        StorageClientProviderInterface $secondClientProvider,
        StorageClientInterface $secondClient,
        StorageInterface $secondStorage,
    ) {
        $firstClientProvider->supports($secondStorage)->willReturn(false);
        $secondClientProvider->supports($secondStorage)->willReturn(true);
        $secondClientProvider->getFromStorage($secondStorage)->shouldBeCalledOnce()->willReturn($secondClient);

        $this->getFromStorage($secondStorage)->shouldReturn($secondClient);
    }

    public function it_throws_an_exception_when_no_client_provider_support_the_storage_configuration(
        StorageInterface $storage,
        StorageClientProviderInterface $firstClientProvider,
        StorageClientProviderInterface $secondClientProvider,
    ) {
        $firstClientProvider->supports($storage)->willReturn(false);
        $secondClientProvider->supports($storage)->willReturn(false);

        $this->shouldThrow(\RuntimeException::class)
            ->during('getFromStorage', [$storage]);
    }
}
