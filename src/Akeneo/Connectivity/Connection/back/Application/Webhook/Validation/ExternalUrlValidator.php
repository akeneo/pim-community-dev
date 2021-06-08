<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalUrlValidator extends ConstraintValidator
{
    private DnsLookupInterface $dnsLookup;

    public function __construct(
        DnsLookupInterface $dnsLookup
    ) {
        $this->dnsLookup = $dnsLookup;
    }

    public function validate($value, Constraint $constraint)
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

        $ip = $this->dnsLookup->ip($host);

        if (null === $ip) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ address }}', $this->formatValue($value))
                ->addViolation();

            return;
        }

        $flag = \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE;
        if (!filter_var($ip, \FILTER_VALIDATE_IP, $flag)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ address }}', $this->formatValue($value))
                ->addViolation();

            return;
        }
    }

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
