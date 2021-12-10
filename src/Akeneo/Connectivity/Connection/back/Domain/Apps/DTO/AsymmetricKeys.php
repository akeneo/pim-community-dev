<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsymmetricKeys
{
    private function __construct(private string $publicKey, private string $privateKey)
    {
    }

    public static function create(string $publicKey, string $privateKey):self
    {
        return new self($publicKey, $privateKey);
    }

    /**
     * @return array{public_key:string,private_key:string}
     */
    public function toArray(): array
    {
        return [
            'public_key' => $this->publicKey,
            'private_key' => $this->privateKey
        ];
    }
}
