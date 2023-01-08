<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

use Akeneo\Tool\Component\Batch\Model\JobInstance;

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

    public function obfuscateCredentials(array $data): array
    {
        foreach ($this->credentialsEncrypters as $credentialsEncrypter) {
            if ($credentialsEncrypter->support($data)) {
                return $credentialsEncrypter->obfuscateCredentials($data);
            }
        }

        return $data;
    }

    public function mergeCredentials(JobInstance $initialJobInstance, array $data)
    {
        foreach ($this->credentialsEncrypters as $credentialsEncrypter) {
            if ($credentialsEncrypter->support($data)) {
                return $credentialsEncrypter->mergeCredentials($initialJobInstance, $data);
            }
        }

        return $data;
    }
}
