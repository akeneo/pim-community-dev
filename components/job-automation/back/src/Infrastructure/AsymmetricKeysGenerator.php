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

namespace Akeneo\Platform\JobAutomation\Infrastructure;

use Akeneo\Platform\JobAutomation\Domain\AsymmetricKeysGeneratorInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use phpseclib3\Crypt\RSA;

class AsymmetricKeysGenerator implements AsymmetricKeysGeneratorInterface
{
    public function __construct(private readonly string $openSSLConfigPath)
    {
    }

    public function generate(): AsymmetricKeys
    {
        RSA::setOpenSSLConfigPath($this->openSSLConfigPath);

        $privateKey = RSA::createKey();
        $privateKey = $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
        $publicKey = $privateKey->getPublicKey();

        return AsymmetricKeys::create($publicKey->toString('OpenSSH'), $privateKey->toString('OpenSSH'));
    }
}
