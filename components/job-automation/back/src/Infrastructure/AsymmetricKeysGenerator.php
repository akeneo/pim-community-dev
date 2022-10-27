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
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;

class AsymmetricKeysGenerator implements AsymmetricKeysGeneratorInterface
{
    public function generate(): AsymmetricKeys
    {
        $privKey = new RSA();
        $keys = $privKey->createKey();
        $privateKey = $keys['privatekey'];
        $publicKey = $keys['publickey'];

        $pubKey = new RSA();
        $pubKey->loadKey($publicKey);
        $pubKey->setPublicKey();

        $subject = new X509();
        $subject->setEndDate('99991231235959Z');
        $subject->setDNProp('id-at-organizationName', 'Akeneo');
        $subject->setPublicKey($pubKey);

        $issuer = new X509();
        $issuer->setPrivateKey($privKey);
        $issuer->setDn($subject->getDN());

        $x509 = new X509();
        $result = $x509->sign($issuer, $subject);

        return AsymmetricKeys::create($x509->saveX509($result), $privateKey);
    }
}
