<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotPrivateNetworkUrlValidator extends ConstraintValidator
{
    private DnsLookupInterface $dnsLookup;

    public function __construct(DnsLookupInterface $dnsLookup)
    {
        $this->dnsLookup = $dnsLookup;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotPrivateNetworkUrl) {
            throw new UnexpectedTypeException($constraint, NotPrivateNetworkUrl::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string) $value;
        if ('' === $value) {
            return;
        }

        $host = parse_url($value, \PHP_URL_HOST);
        if (!is_string($host)) {
            return;
        }

        if (!$ip = $this->dnsLookup->lookupHost($host)) {
            $this->context->buildViolation($constraint->unresolvableHostMessage)
                ->setParameter('{{ host }}', $this->formatValue($host))
                ->addViolation();

            return;
        }
        $ip = $ip[0];

        $flag = \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE;
        if (!filter_var($ip, \FILTER_VALIDATE_IP, $flag)) {
            $this->context->buildViolation($constraint->ipBlockedMessage)
                ->setParameter('{{ ip }}', $this->formatValue($ip))
                ->setParameter('{{ url }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
