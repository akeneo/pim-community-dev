<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Inspired by https://github.com/FriendsOfSymfony/oauth2-php/blob/546f869d68fb79b284752e6787263d797165dba4/lib/OAuth2.php#L1319
 */
class RandomCodeGenerator implements RandomCodeGeneratorInterface
{
    public function generate(): string
    {
        if (@\file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = \file_get_contents('/dev/urandom', false, null, 0, 100);
        } elseif (\function_exists('openssl_random_pseudo_bytes')) { // Get 100 bytes of pseudo-random data
            $bytes = \openssl_random_pseudo_bytes(100, $strong);
            if (true === $strong && false !== $bytes) {
                $randomData = $bytes;
            }
        }
        // Last resort: mt_rand
        if (empty($randomData)) { // Get 108 bytes of (pseudo-random, insecure) data
            $randomData = \random_int(0, \mt_getrandmax())
                . \random_int(0, \mt_getrandmax())
                . \random_int(0, \mt_getrandmax())
                . \uniqid((string) \random_int(0, \mt_getrandmax()), true)
                . \microtime(true)
                . \uniqid((string) \random_int(0, \mt_getrandmax()), true);
        }

        return \rtrim(\strtr(\base64_encode(\hash('sha256', $randomData)), '+/', '-_'), '=');
    }
}
