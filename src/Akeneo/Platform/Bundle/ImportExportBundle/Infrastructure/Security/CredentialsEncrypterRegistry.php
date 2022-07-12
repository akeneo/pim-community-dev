<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

class CredentialsEncrypterRegistry
{
    /** @var CredentialsEncrypter[] */
    private $credentialsEncrypters = [];

    public function register(CredentialsEncrypter $credentialsEncrypter)
    {
        $this->credentialsEncrypters[] = $credentialsEncrypter;
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
