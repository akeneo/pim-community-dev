<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Domain\Model;

class AsymmetricKeys
{
    public const PUBLIC_KEY = 'public_key';
    public const PRIVATE_KEY = 'private_key';

    private function __construct(private string $publicKey, private string $privateKey)
    {
    }

    public static function create(string $publicKey, string $privateKey): self
    {
        return new self($publicKey, $privateKey);
    }

    /**
     * @return array{public_key:string,private_key:string}
     */
    public function normalize(): array
    {
        return [
            self::PUBLIC_KEY => $this->publicKey,
            self::PRIVATE_KEY => $this->privateKey,
        ];
    }
}
