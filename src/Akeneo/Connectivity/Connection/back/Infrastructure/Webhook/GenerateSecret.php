<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateSecret implements GenerateSecretInterface
{
    public function generate(): string
    {
        return self::randomBase36(50);
    }

    /**
     * base_convert() works on floats for large numbers: on a 256 or 128-bit input it silently loses
     * most of the entropy (and PHP 8.6 raises a notice), so we draw each base36 character directly.
     */
    private static function randomBase36(int $length): string
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $alphabet[\random_int(0, 35)];
        }

        return $result;
    }
}
