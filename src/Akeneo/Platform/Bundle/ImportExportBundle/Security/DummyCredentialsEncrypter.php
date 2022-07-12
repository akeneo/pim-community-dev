<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Security;

class DummyCredentialsEncrypter implements CredentialsEncrypter
{
    public function encryptCredentials(array $data): array
    {
        return $data;
    }
}
