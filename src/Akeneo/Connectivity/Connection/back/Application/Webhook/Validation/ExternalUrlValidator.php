<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalUrlValidator extends ConstraintValidator
{
    private const DOMAIN_BLACKLIST = [
        'localhost',
        'elasticsearch',
        'memcached',
    ];

    private DnsLookupInterface $dnsLookup;

    /**
     * @var string[]
     */
    private array $networkWhitelist;

    public function __construct(
        DnsLookupInterface $dnsLookup,
        string $networkWhitelist = ''
    ) {
        $this->dnsLookup = $dnsLookup;
        $this->networkWhitelist = explode(',', $networkWhitelist);
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExternalUrl) {
            throw new UnexpectedTypeException($constraint, ExternalUrl::class);
        }

        $value = $this->valueToString($value);
        if (empty($value)) {
            return;
        }

        $host = parse_url($value, \PHP_URL_HOST);
        if (empty($host) || !is_string($host)) {
            return;
        }

        if (in_array($host, self::DOMAIN_BLACKLIST)) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }

        $ip = $this->dnsLookup->ip($host);

        if (null === $ip) {
            return;
        }

        if ($this->isInWhitelist($ip)) {
            return;
        }

        if ($this->isInPrivateRange($ip)) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }
    }

    private function isInWhitelist(string $ip): bool
    {
        if (empty($this->networkWhitelist)) {
            return false;
        }

        return IpUtils::checkIp($ip, $this->networkWhitelist);
    }

    private function isInPrivateRange(string $ip): bool
    {
        return !filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE);
    }

    /**
     * @param mixed $value
     */
    private function valueToString($value): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (\is_object($value) && method_exists($value, '__toString')) {
            return $value->__toString();
        }

        return '';
    }
}
