<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @author Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ChannelCategoryHasBeenUpdated extends Event
{
    /** @var string */
    private $channelCode;

    /** @var string */
    private $previousCategoryCode;

    /** @var string */
    private $newCategoryCode;

    public function __construct(string $channelCode, string $previousCategoryCode, string $newCategoryCode)
    {
        $this->channelCode = $channelCode;
        $this->newCategoryCode = $newCategoryCode;
        $this->previousCategoryCode = $previousCategoryCode;
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function previousCategoryCode(): string
    {
        return $this->previousCategoryCode;
    }

    public function newCategoryCode(): string
    {
        return $this->newCategoryCode;
    }
}
