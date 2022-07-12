<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

interface CredentialsEncrypter
{
    public function encryptCredentials(array $data): array;
    public function support(array $data): bool;
}
