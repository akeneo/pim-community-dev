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
    private string $action;

    private string $eventId;

    private string $eventDateTime;

    /** @var array<mixed> */
    private array $data;

    private Author $author;

    private string $pimSource;

    private EventInterface $pimEvent;

    /**
     * @param array<mixed> $data
     */
    public function __construct(
        string $action,
        string $eventId,
        string $eventDateTime,
        Author $author,
        string $pimSource,
        array $data,
        EventInterface $pimEvent
    ) {
        $this->action = $action;
        $this->eventId = $eventId;
        $this->eventDateTime = $eventDateTime;
        $this->data = $data;
        $this->author = $author;
        $this->pimSource = $pimSource;
        $this->pimEvent = $pimEvent;
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
