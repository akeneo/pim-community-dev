<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Locale;

use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetChannelLocalesQuery implements GetChannelLocalesQueryInterface
{
    public function __construct(private ChannelRepositoryInterface $channelRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $channelCode): array
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);

        if (null === $channel) {
            throw new \LogicException(\sprintf('channel "%s" does not exist', $channelCode));
        }

        return \array_map(static fn (LocaleInterface $locale): array => [
            'code' => $locale->getCode(),
            'label' => $locale->getName() ?? \sprintf('[%s]', $locale->getCode()),
        ], $channel->getLocales()->toArray());
    }
}
