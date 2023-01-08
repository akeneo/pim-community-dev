<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

use Akeneo\Tool\Component\Batch\Model\JobInstance;

interface CredentialsEncrypter
{
    public function encryptCredentials(array $data): array;

    public function decryptCredentials(array $data): array;

    public function obfuscateCredentials(array $data): array;

    public function mergeCredentials(JobInstance $initialJobInstance, array $data): array;

    public function support(array $data): bool;
}
