<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Tenant\Domain\Values;

use Akeneo\Platform\Component\Tenant\Domain\ContextValueDecrypterInterface;
use Webmozart\Assert\Assert;

/**
 * Value object for a tenant encrypted value
 *
 * @author  JM Leroux <jmleroux.pro@gmail.com>
 */
final class EncryptedValues
{
    private function __construct(
        private readonly string $data,
        private readonly string $iv,
    ) {
    }

    public static function create(array $encryptedPayload): self
    {
        Assert::keyExists($encryptedPayload, 'data', 'Encrypted payload must have a "data" key');
        Assert::keyExists($encryptedPayload, 'iv', 'Encrypted payload must have an "iv" key');

        return new self(
            $encryptedPayload['data'],
            $encryptedPayload['iv'],
        );
    }

    public function decode(ContextValueDecrypterInterface $decoder): array
    {
        return $decoder->decode($this->data, $this->iv);
    }

    public function normalize(): array
    {
        return [
            'data' => $this->data,
            'iv' => $this->iv,
        ];
    }
}
