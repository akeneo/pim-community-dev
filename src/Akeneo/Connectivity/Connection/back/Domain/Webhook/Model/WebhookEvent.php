<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEvent
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(
        private string $action,
        private string $eventId,
        private string $eventDateTime,
        private Author $author,
        private string $pimSource,
        private array $data,
        private EventInterface $pimEvent
    ) {
    }

    public function action(): string
    {
        return $this->action;
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function eventDateTime(): string
    {
        return $this->eventDateTime;
    }

    public function author(): Author
    {
        return $this->author;
    }

    public function pimSource(): string
    {
        return $this->pimSource;
    }

    /**
     * @return array<mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    public function getPimEvent(): EventInterface
    {
        return $this->pimEvent;
    }
}
