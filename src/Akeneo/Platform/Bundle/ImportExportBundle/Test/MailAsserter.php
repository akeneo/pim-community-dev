<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MailAsserter
{
    use MailerAssertionsTrait;

    public static function assertEmailHasBeenSentToAddress(string $espectedAddress): bool
    {
        $mailerMessages = self::getMailerMessages();

        foreach ($mailerMessages as $mailerMessage) {
            if(!$mailerMessage instanceof Email) {
                continue;
            }

            $addressesWhichHasReceivedAnEmail = array_map(static fn (Address $address) => $address->getAddress(), $mailerMessage->getBcc());

            return in_array($espectedAddress, $addressesWhichHasReceivedAnEmail);
        }

        return false;
    }
}
