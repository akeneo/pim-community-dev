<?php

namespace Akeneo\Tool\Component\Email;

/**
 * Address mail where to send the emails regarding the batches
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class SenderAddress
{
    /** @var string */
    private $senderAddress;

    private function __construct(string $senderAddress)
    {
        $this->senderAddress = $senderAddress;
    }

    public static function fromMailerUrl(string $mailerUrl): SenderAddress
    {
        $query = parse_url($mailerUrl, PHP_URL_QUERY);
        if (null === $query) {
            throw new \InvalidArgumentException('Cannot create a SenderAddress with an invalid mailer URL.');
        }

        parse_str($query, $queryParts);
        if (!isset($queryParts['sender_address'])) {
            throw new \InvalidArgumentException('Cannot create a SenderAddress if it\'s missing from the URL.');
        }

        if (false === filter_var($queryParts['sender_address'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Cannot create a SenderAddress if a valid email is not given.');
        }

        return new self($queryParts['sender_address']);
    }

    public function __toString(): string
    {
        return $this->senderAddress;
    }
}
