<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Channel;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelsQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetChannelsQuery implements GetChannelsQueryInterface
{
    public function __construct(private ChannelRepositoryInterface $channelRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(int $page = 1, int $limit = 20): array
    {
        /** @var array<ChannelInterface> $channels */
        $channels = $this->channelRepository->findBy(
            [],
            [],
            $limit,
            ($page - 1) * $limit
        );

        return \array_map(
            static fn (ChannelInterface $channel): array => [
                'code' => $channel->getCode(),
                'label' => $channel->getLabel(),
            ],
            $channels
        );
    }
}
