<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetChannelCodeWithLocaleCodes implements GetChannelCodeWithLocaleCodesInterface
{
    /** @var InMemoryChannelRepository */
    private $channelRepository;

    public function __construct(InMemoryChannelRepository $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function findAll(): array
    {
        return array_map(function (ChannelInterface $channel): array {
            return [
                'channelCode' => $channel->getCode(),
                'localeCodes' => $channel->getLocaleCodes(),
            ];
        }, $this->channelRepository->findAll());
    }
}
