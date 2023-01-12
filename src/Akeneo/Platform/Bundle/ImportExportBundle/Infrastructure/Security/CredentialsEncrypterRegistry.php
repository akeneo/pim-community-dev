<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

final class CredentialsEncrypterRegistry
{
    public function __construct(
        /** @var $credentialsEncrypters CredentialsEncrypter[] */
        private readonly iterable $credentialsEncrypters,
    ) {
    }

    public function encryptCredentials(array $previousData, array $data): array
    {
        foreach ($this->credentialsEncrypters as $credentialsEncrypter) {
            if ($credentialsEncrypter->support($data)) {
                return $credentialsEncrypter->encryptCredentials($previousData, $data);
            }
        }

        return $data;
    }

    public function obfuscateCredentials(array $data): array
    {
        foreach ($this->credentialsEncrypters as $credentialsEncrypter) {
            if ($credentialsEncrypter->support($data)) {
                return $credentialsEncrypter->obfuscateCredentials($data);
            }
        }

        return $data;
    }
}
