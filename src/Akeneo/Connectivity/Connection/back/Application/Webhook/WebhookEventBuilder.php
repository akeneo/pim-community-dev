<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilder
{
    /** @var iterable<EventDataBuilderInterface> */
    private $builders;

    /**
     * @param iterable<EventDataBuilderInterface> $builders
     */
    public function __construct(iterable $builders)
    {
        $this->builders = $builders;
    }

    /**
     * @param BusinessEventInterface $businessEvent
     * @param array<mixed> $context
     *
     * @return WebhookEvent
     */
    public function build(BusinessEventInterface $businessEvent, array $context = []): WebhookEvent
    {
        $context = $this->resolveOptions($context);

        return new WebhookEvent(
            $businessEvent->name(),
            $businessEvent->uuid(),
            date(\DateTimeInterface::ATOM, $businessEvent->timestamp()),
            $businessEvent->author(),
            $context['pim_source'],
            $this->buildEventData($businessEvent, $context)
        );
    }

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['user', 'pim_source']);
        $resolver->setAllowedTypes('pim_source', 'string');
        $resolver->setAllowedValues('pim_source', function ($value) {
            return !empty($value);
        });
        $resolver->setAllowedTypes('user', UserInterface::class);

        return $resolver->resolve($options);
    }


    /**
     * @param BusinessEventInterface $businessEvent
     * @param array<mixed> $context
     *
     * @return array<mixed>
     */
    private function buildEventData(BusinessEventInterface $businessEvent, array $context): array
    {
        foreach ($this->builders as $builder) {
            if (true === $builder->supports($businessEvent)) {
                return $builder->build($businessEvent, $context);
            }
        }

        throw new WebhookEventDataBuilderNotFoundException($businessEvent->name());
    }
}
