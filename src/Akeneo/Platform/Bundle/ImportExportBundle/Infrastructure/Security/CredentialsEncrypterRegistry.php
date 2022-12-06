<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

final class CredentialsEncrypterRegistry
{
    public function __construct(
        /** @var $credentialsEncrypters CredentialsEncrypter[] */
        private readonly iterable $credentialsEncrypters,
    ) {
    }

    public function encryptCredentials(array $data): array
    {
        foreach ($this->credentialsEncrypters as $credentialsEncrypter) {
            if ($credentialsEncrypter->support($data)) {
                return $credentialsEncrypter->encryptCredentials($data);
            }
        }

        return $data;
    }

    public function decryptCredentials(array $data): array
    {
        foreach ($this->credentialsEncrypters as $credentialsEncrypter) {
            if ($credentialsEncrypter->support($data)) {
                return $credentialsEncrypter->decryptCredentials($data);
            }
        }

        return $data;
    }
}
