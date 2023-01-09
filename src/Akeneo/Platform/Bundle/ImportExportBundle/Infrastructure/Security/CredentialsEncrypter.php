<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

interface CredentialsEncrypter
{
    public function encryptCredentials(array $previousData, array $data): array;

    public function decryptCredentials(array $data): array;

    public function obfuscateCredentials(array $data): array;

    public function support(array $data): bool;
}
