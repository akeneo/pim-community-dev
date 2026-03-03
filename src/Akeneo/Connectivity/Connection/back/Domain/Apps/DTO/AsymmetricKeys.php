<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
