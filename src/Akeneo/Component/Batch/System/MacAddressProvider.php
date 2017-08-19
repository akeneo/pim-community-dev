<?php

declare(strict_types=1);

namespace Akeneo\Component\Batch\System;

/**
 * This class aims to get the MAC address as the system id.
 *
 * Inspired by Ramsey uuid library https://github.com/ramsey/uuid.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see https://github.com/ramsey/uuid/blob/master/src/Provider/Node/SystemNodeProvider.php
 */
class MacAddressProvider implements SystemIdProvider
{
    /**
     * Returns the first MAC address found from the ifconfig output.
     *
     * @return string Mac address a an hexadecimal string, empty string if not found
     */
    public function getSystemId() : string
    {
        $pattern = '/[^:]([0-9A-Fa-f]{2}([:-])[0-9A-Fa-f]{2}(\2[0-9A-Fa-f]{2}){4})[^:]/';
        $matches = [];

        $node = '';
        if (preg_match_all($pattern, $this->getIfconfig(), $matches, PREG_PATTERN_ORDER)) {
            $node = $matches[1][0];
            $node = str_replace([':', '-'], '', $node);
        }

        return $node;
    }

    /**
     * Returns the network interface configuration for the system.
     *
     * @return string
     */
    protected function getIfconfig() : string
    {
        ob_start();
        switch (strtoupper(substr(php_uname('a'), 0, 3))) {
            case 'WIN':
                passthru('ipconfig /all 2>&1');
                break;
            case 'DAR':
                passthru('ifconfig 2>&1');
                break;
            case 'LIN':
            default:
                passthru('netstat -ie 2>&1');
                break;
        }

        return ob_get_clean();
    }
}
