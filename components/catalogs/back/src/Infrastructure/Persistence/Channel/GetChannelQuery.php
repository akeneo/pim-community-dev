<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Channel;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetChannelQuery implements GetChannelQueryInterface
{
    public function __construct(private ChannelRepositoryInterface $channelRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $code): ?array
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneByIdentifier($code);

        if (null === $channel) {
            return null;
        }

        return [
            'code' => $channel->getCode(),
            'label' => $channel->getLabel(),
        ];
    }
}
