<?php

namespace Oro\Bundle\EmailBundle\Entity\Util;

class EmailUtil
{
    /**
     * Extract 'pure' email address from the given email address
     *
     * Examples:
     *    email address: "John Smith" <john@example.com>; 'pure' email address john@example.com
     *    email address: John Smith <john@example.com>; 'pure' email address john@example.com
     *    email address: <john@example.com>; 'pure' email address john@example.com
     *    email address: john@example.com; 'pure' email address john@example.com
     *
     * @param string $fullEmailAddress
     * @return string
     */
    public static function extractPureEmailAddress($fullEmailAddress)
    {
        $atPos = strpos($fullEmailAddress, '@');
        if ($atPos === false) {
            return $fullEmailAddress;
        }

        $startPos = strrpos($fullEmailAddress, '<', -$atPos);
        if ($startPos === false) {
            return $fullEmailAddress;
        }

        $endPos = strpos($fullEmailAddress, '>', $atPos);
        if ($endPos === false) {
            return $fullEmailAddress;
        }

        return substr($fullEmailAddress, $startPos + 1, $endPos - $startPos - 1);
    }

    /**
     * Return current UTC date/time
     *
     * @return \DateTime
     */
    public static function currentUTCDateTime()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
