<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ChannelCategoryHasBeenUpdated extends Event
{
    public const EVENT_NAME = 'CHANNEL_CATEGORY_HAS_BEEN_UPDATED';

    /** @var string */
    private $channelCode;

    /** @var string */
    private $categoryCode;

    public function __construct(string $channelCode, string $categoryCode)
    {
        $this->channelCode = $channelCode;
        $this->categoryCode = $categoryCode;
    }

    public function getChannelCode(): string
    {
        return $this->channelCode;
    }

    public function getCategoryCode(): string
    {
        return $this->categoryCode;
    }
}
