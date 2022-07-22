<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Security;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypter;
use Akeneo\Platform\JobAutomation\Domain\Model\SftpStorage;

class SftpCredentialsEncrypter implements CredentialsEncrypter
{
    public function __construct(
        private Encrypter $encrypter,
    ) {
    }

    public function encryptCredentials(array $data): array
    {
        $encryptionKey = $this->getEncryptionKey($data['configuration']['storage']);

        $data['configuration']['storage']['password'] = $this->encrypter->encrypt(
            $data['configuration']['storage']['password'],
            $encryptionKey,
        );

        return $data;
    }

    public function decryptCredentials(array $data): array
    {
        $encryptionKey = $this->getEncryptionKey($data['configuration']['storage']);

        $data['configuration']['storage']['password'] = $this->encrypter->decrypt(
            $data['configuration']['storage']['password'],
            $encryptionKey,
        );

        return $data;
    }

    public function support(array $data): bool
    {
        return isset($data['configuration']['storage']['type'])
            && isset($data['configuration']['storage']['username'])
            && isset($data['configuration']['storage']['host'])
            && isset($data['configuration']['storage']['port'])
            && SftpStorage::TYPE === $data['configuration']['storage']['type']
        ;
    }

    private function getEncryptionKey(array $storage): string
    {
        $encryptionKey = sprintf(
            '%s@%s:%s',
            $storage['username'],
            $storage['host'],
            $storage['port'],
        );

        return $encryptionKey;
    }
}
