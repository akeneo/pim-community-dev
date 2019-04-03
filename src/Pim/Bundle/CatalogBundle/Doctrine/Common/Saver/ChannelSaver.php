<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\EventSubscriber\Event\DeactivatedLocalesOnChannel;
use Pim\Bundle\CatalogBundle\EventSubscriber\Event\DeactivatedLocalesOnChannelsEvent;
use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ChannelSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var Connection */
    private $connection;

    /**
     * @param ObjectManager                  $objectManager
     * @param EventDispatcherInterface       $eventDispatcher
     * @param Connection                     $connection
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        Connection $connection
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        $this->validateChannel($object);

        $options['unitary'] = true;
        $options['is_new'] = null === $object->getId();

        $previousLocales = [];

        if (false === $options['is_new']) {
            $previousLocales = $this->getActivatedLocalesByChannel([$object])[$object->getId()];
        }

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($object, $options));

        $this->objectManager->persist($object);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($object, $options));

        $newLocales = $object->getLocales()->map(function (Locale $locale) {
            return $locale->getId();
        })->toArray();

        $deactivatedLocales = array_diff($previousLocales, $newLocales);

        $this->eventDispatcher->dispatch(
            DeactivatedLocalesOnChannel::NAME,
            new DeactivatedLocalesOnChannelsEvent([new DeactivatedLocalesOnChannel($object->getId(), $deactivatedLocales)])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($objects, $options));

        $areObjectsNew = array_map(function ($object) {
            return null === $object->getId();
        }, $objects);

        $oldChannels = array_filter($objects, function ($object) {
           return null !== $object->getId();
        });

        foreach ($objects as $i => $object) {
            $this->validateChannel($object);

            $this->eventDispatcher->dispatch(
                StorageEvents::PRE_SAVE,
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]]))
            );

            $this->objectManager->persist($object);
        }

        $previousLocalesByChannels = $this->getActivatedLocalesByChannel($oldChannels);

        $this->objectManager->flush();

        foreach ($objects as $i => $object) {
            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE,
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]]))
            );
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($objects, $options));

        $removedLocalesByChannels = [];
        foreach ($oldChannels as $channel) {
            $newLocales = $channel->getLocales()->map(function (Locale $locale) {
                return $locale->getId();
            })->toArray();

            $deactivatedLocales = array_diff($previousLocalesByChannels[$channel->getId()], $newLocales);
            $removedLocalesByChannels[] = new DeactivatedLocalesOnChannel($channel->getId(), $deactivatedLocales);
        }

        $this->eventDispatcher->dispatch(DeactivatedLocalesOnChannelsEvent::NAME, new DeactivatedLocalesOnChannelsEvent($removedLocalesByChannels));
    }

    /**
     * @param array $channels
     * @return array ['channel1' => ['locale1', 'locale2']]
     */
    private function getActivatedLocalesByChannel(array $channels): array {
        if (count($channels) < 1) {
            return [];
        }

        $ids = array_map(function (ChannelInterface $channel) {
            return $channel->getId();
        } , $channels);

        $channelsAndLocales = $this->connection->fetchAll('SELECT channel_id, GROUP_CONCAT(locale_id SEPARATOR \',\') as locales FROM pim_catalog_channel_locale WHERE channel_id IN (?) GROUP BY channel_id', [$ids], [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY]);
        $result = [];

        foreach ($channelsAndLocales as $channelsAndLocale) {
            $result[$channelsAndLocale['channel_id']] = explode(',', $channelsAndLocale['locales']);
        }

        return $result;
    }

    /**
     * @param $object
     */
    private function validateChannel($object)
    {
        if (!$object instanceof ChannelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    ChannelInterface::class,
                    ClassUtils::getClass($object)
                )
            );
        }
    }
}
