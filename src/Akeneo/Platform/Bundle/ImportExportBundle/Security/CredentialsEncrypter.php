<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Security;

interface CredentialsEncrypter
{
    public function encryptCredentials(array $data): array;
}
