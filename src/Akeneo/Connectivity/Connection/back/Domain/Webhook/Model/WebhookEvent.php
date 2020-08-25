<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEvent
{
    /** @var string */
    private $action;

    /** @var string */
    private $eventId;

    /** @var string */
    private $eventDate;

    /** @var array<mixed> */
    private $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(string $action, string $eventId, string $eventDate, array $data)
    {
        $this->action = $action;
        $this->eventId = $eventId;
        $this->eventDate = $eventDate;
        $this->data = $data;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function eventDate(): string
    {
        return $this->eventDate;
    }

    /**
     * @return array<mixed>
     */
    public function data(): array
    {
        return $this->data;
    }
}
