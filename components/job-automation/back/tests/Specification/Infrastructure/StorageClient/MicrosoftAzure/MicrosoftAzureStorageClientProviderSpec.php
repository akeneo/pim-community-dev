<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\MicrosoftAzure;

use Akeneo\Platform\JobAutomation\Domain\Model\Storage\MicrosoftAzureStorage;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\SftpStorage;
use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\MicrosoftAzure\MicrosoftAzureStorageClient;
use PhpSpec\ObjectBehavior;

class MicrosoftAzureStorageClientProviderSpec extends ObjectBehavior
{
    public function let(
        Encrypter $encrypter,
    )
    {
        $this->beConstructedWith($encrypter);
    }

    public function it_gets_client_from_storage(
        Encrypter $encrypter
    ): void {
        $azureStorage = new MicrosoftAzureStorage(
            'a_connection_string',
            'a_container_name',
            '/tmp/products.xlsx',
        );

        $encrypter->decrypt('a_connection_string', 'a_container_name')
            ->willReturn('DefaultEndpointsProtocol=https;AccountName=coucou;AccountKey=kro;EndpointSuffix=core.windows.net');

        $this->getFromStorage($azureStorage)->shouldBeAnInstanceOf(MicrosoftAzureStorageClient::class);
    }

    public function it_supports_only_microsoft_azure_storage(): void
    {
        $this->supports(new MicrosoftAzureStorage(
            'a_connection_string',
            'a_container_name',
            '/tmp/products.xlsx',
        ))->shouldReturn(true);

        $this->supports(new SftpStorage(
            'an_host',
            22,
            'a_login_type',
            'a_username',
            null,
            'a_file_path',
            null,
            null,
            null,
        ))->shouldReturn(false);
    }
}
