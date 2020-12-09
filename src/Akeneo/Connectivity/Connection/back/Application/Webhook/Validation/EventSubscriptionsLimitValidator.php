<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionsLimitValidator extends ConstraintValidator
{
    private SelectActiveWebhooksQuery $selectActiveWebhooksQuery;
    private int $activeEventSubscriptionsLimit;

    public function __construct(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        int $activeEventSubscriptionsLimit
    ) {
        $this->selectActiveWebhooksQuery = $selectActiveWebhooksQuery;
        $this->activeEventSubscriptionsLimit = $activeEventSubscriptionsLimit;
    }

    public function validate($eventSubscription, Constraint $constraint): void
    {
        if (!$constraint instanceof EventSubscriptionsLimit) {
            throw new UnexpectedTypeException($constraint, EventSubscriptionsLimit::class);
        }

        if (!$eventSubscription instanceof ConnectionWebhook) {
            throw new UnexpectedValueException($eventSubscription, ConnectionWebhook::class);
        }

        // Skip the limit check if the event subscription is disabled.
        if (false === $eventSubscription->enabled()) {
            return;
        }

        // Count the number of active event subscriptions but ignore the current one if it is already enabled.
        $activeEventSubscriptionsCount = count(
            array_filter(
                $this->selectActiveWebhooksQuery->execute(),
                fn (ActiveWebhook $activeEventSubscription) => $activeEventSubscription->connectionCode() !== $eventSubscription->code(),
            ),
        );

        // Check if the limit is already reached.
        if ($activeEventSubscriptionsCount >= $this->activeEventSubscriptionsLimit) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('enabled')
                ->addViolation();
        }
    }
}
