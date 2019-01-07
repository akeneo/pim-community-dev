<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Channel\ChannelUpdateSubscriber;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ChannelContext implements Context
{
    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var ObjectUpdaterInterface */
    private $channelUpdater;

    /** @var ChannelUpdateSubscriber */
    private $deactivateAllEnglishLocalesSubscriber;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param ObjectUpdaterInterface $channelUpdater
     * @param ChannelUpdateSubscriber $deactivateAllEnglishLocalesSubscriber
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        ObjectUpdaterInterface $channelUpdater,
        ChannelUpdateSubscriber $deactivateAllEnglishLocalesSubscriber
    ) {
        $this->channelRepository = $channelRepository;
        $this->channelUpdater = $channelUpdater;
        $this->deactivateAllEnglishLocalesSubscriber = $deactivateAllEnglishLocalesSubscriber;
    }

    /**
     * @Then a product manager deactivates all english locales
     */
    public function deactivatesAllEnglishLocales(): void
    {
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            $this->channelUpdater->update($channel, ['locales' => ['fr_FR']]);
        }

        $this->deactivateAllEnglishLocalesSubscriber->onPostSave(new GenericEvent($channel));
    }
}
