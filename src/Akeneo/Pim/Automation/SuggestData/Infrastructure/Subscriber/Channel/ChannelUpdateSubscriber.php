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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Channel;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\DeactivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\DeactivateConnectionHandler;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ChannelUpdateSubscriber implements EventSubscriberInterface
{
    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var DeactivateConnectionHandler */
    private $deactivateConnectionHandler;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param DeactivateConnectionHandler $deactivateConnectionHandler
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        DeactivateConnectionHandler $deactivateConnectionHandler
    ) {
        $this->localeRepository = $localeRepository;
        $this->deactivateConnectionHandler = $deactivateConnectionHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'onPostSave',
        ];
    }

    /**
     * Deactivates suggest data if all english locales are deactivated.
     *
     * @param GenericEvent $event
     */
    public function onPostSave(GenericEvent $event): void
    {
        if (!$event->getSubject() instanceof ChannelInterface) {
            return;
        }

        if ($this->noEnglishLocaleIsActivated()) {
            $this->deactivateConnectionHandler->handle(new DeactivateConnectionCommand());
        }
    }

    /**
     * Checks if there is at least one english locale (meaning "en_**") among the activated locales.
     *
     * @return bool
     */
    private function noEnglishLocaleIsActivated(): bool
    {
        $activatedLocaleCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($activatedLocaleCodes as $activatedLocaleCode) {
            if (1 === preg_match('/^en_.*/', $activatedLocaleCode)) {
                return false;
            }
        }

        return true;
    }
}
