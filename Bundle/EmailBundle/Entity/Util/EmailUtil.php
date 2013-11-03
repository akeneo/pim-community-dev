<?php

namespace Oro\Bundle\EmailBundle\Entity\Util;

use Oro\Bundle\EmailBundle\Entity\EmailInterface;

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
        $atPos = strrpos($fullEmailAddress, '@');
        if ($atPos === false) {
            return $fullEmailAddress;
        }

        $startPos = strrpos($fullEmailAddress, '<', -(strlen($fullEmailAddress) - $atPos));
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
     * Extract email address name from the given email address
     *
     * Examples:
     *    email address: "John Smith" <john@example.com>; email address name John Smith
     *    email address: John Smith <john@example.com>; email address name John Smith
     *    email address: <john@example.com>; email address name is null
     *    email address: john@example.com; email address name is null
     *
     * @param string $fullEmailAddress
     * @return string|null
     */
    public static function extractEmailAddressName($fullEmailAddress)
    {
        $addrPos = strrpos($fullEmailAddress, '<');
        if ($addrPos === false) {
            return null;
        }

        $result = trim(substr($fullEmailAddress, 0, $addrPos), ' "\'');

        return empty($result) ? null : $result;
    }

    /**
     * Extract email addresses from the given argument.
     * Always return an array, even if no any email is given.
     *
     * @param $emails
     * @return string[]
     * @throws \InvalidArgumentException
     */
    public static function extractEmailAddresses($emails)
    {
        if (is_string($emails)) {
            return empty($emails)
                ? array()
                : array($emails);
        }
        if (!is_array($emails) && !($emails instanceof \Traversable)) {
            throw new \InvalidArgumentException('The emails argument must be a string, array or collection.');
        }

        $result = array();
        foreach ($emails as $email) {
            if (is_string($email)) {
                $result[] = $email;
            } elseif ($email instanceof EmailInterface) {
                $result[] = $email->getEmail();
            } else {
                throw new \InvalidArgumentException(
                    'Each item of the emails collection must be a string or an object of EmailInterface.'
                );
            }
        }

        return $result;
    }

    /**
     * Build a full email address from the given 'pure' email address and email address owner name
     *
     * Examples of full email addresses:
     *    John Smith <john@example.com>, if 'pure' email address is john@example.com and owner name is 'John Smith'
     *    John <john@example.com>, if 'pure' email address is john@example.com and owner name is 'John'
     *    john@example.com, if 'pure' email address is john@example.com and owner name is empty
     *
     * @param string $pureEmailAddress
     * @param string $emailAddressOwnerName
     * @return string
     */
    public static function buildFullEmailAddress($pureEmailAddress, $emailAddressOwnerName)
    {
        if ($pureEmailAddress === null) {
            $pureEmailAddress = '';
        }

        if (empty($emailAddressOwnerName)) {
            return trim($pureEmailAddress);
        }

        return sprintf('%s <%s>', trim($emailAddressOwnerName), trim($pureEmailAddress));
    }

    /**
     * Determine whether the given string represents a full email address or not.
     * The full email address is an address contains both an name and email parts.
     *
     * @param string $emailAddress
     * @return bool
     */
    public static function isFullEmailAddress($emailAddress)
    {
        if (empty($emailAddress)) {
            return false;
        }

        return (strpos($emailAddress, '<') !== false);
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
