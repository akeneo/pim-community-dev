<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledWebhookRequiresAnUrlValidator extends ConstraintValidator
{
    public function validate($webhook, Constraint $constraint): void
    {
        if (!$constraint instanceof EnabledWebhookRequiresAnUrl) {
            throw new UnexpectedTypeException($constraint, EnabledWebhookRequiresAnUrl::class);
        }
        if (!$webhook instanceof ConnectionWebhook) {
            throw new UnexpectedTypeException($webhook, ConnectionWebhook::class);
        }
        if ($webhook->enabled() && (null === $webhook->url() || '' === (string) $webhook->url())) {
            $this->context->buildViolation($constraint->message)->atPath('url')->addViolation();
        }
    }
}
