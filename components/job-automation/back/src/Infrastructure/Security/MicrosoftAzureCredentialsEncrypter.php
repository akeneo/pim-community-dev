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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Security;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypter;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\MicrosoftAzureStorage;

final class MicrosoftAzureCredentialsEncrypter implements CredentialsEncrypter
{
    public function __construct(
        private readonly Encrypter $encrypter,
    ) {
    }

    public function encryptCredentials(array $data): array
    {
        $encryptionKey = $this->getEncryptionKey($data);

        $data['connection_string'] = $this->encrypter->encrypt($data['connection_string'], $encryptionKey);

        return $data;
    }

    public function decryptCredentials(array $data): array
    {
        $encryptionKey = $this->getEncryptionKey($data);

        $data['connection_string'] = $this->encrypter->decrypt($data['connection_string'], $encryptionKey);

        return $data;
    }

    public function support(array $data): bool
    {
        return isset($data['type'])
            && isset($data['connection_string'])
            && isset($data['container_name'])
            && MicrosoftAzureStorage::TYPE === $data['type']
        ;
    }

    private function getEncryptionKey(array $storage): string
    {
        return sprintf(
            '%s',
            $storage['container_name'],
        );
    }
}
