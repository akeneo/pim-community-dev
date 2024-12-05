<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Auth;

final class StorageSharedKeyCredential
{
    public function __construct(
        public readonly string $accountName,
        public readonly string $accountKey,
    ) {
    }

    public function computeHMACSHA256(string $stringToSign): string
    {
        $decodedAccountKey = base64_decode($this->accountKey, true);

        if ($decodedAccountKey === false) {
            throw new \Exception('Invalid account key.');
        }

        return base64_encode(
            hash_hmac('sha256', $stringToSign, $decodedAccountKey, true),
        );
    }
}
