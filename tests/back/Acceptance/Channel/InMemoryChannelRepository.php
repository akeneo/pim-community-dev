<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class InMemoryChannelRepository implements ChannelRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $channels;

    public function __construct(array $channels = [])
    {
        $this->channels = new ArrayCollection($channels);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->channels->get($code);
    }

    /**
     * {@inheritdoc}
     */
    public function save($channel, array $options = [])
    {
        $this->channels->set($channel->getCode(), $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $channels = [];
        foreach ($this->channels as $channel) {
            $keepThisChannel = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($channel->$getter() !== $value) {
                    $keepThisChannel = false;
                }
            }

            if ($keepThisChannel) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCodes()
    {
        return $this->channels->map(function (ChannelInterface $channel): string {
            return $channel->getCode();
        })->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullChannels()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCountUsingCurrency(CurrencyInterface $currency)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelsIndexedByCode($localeCode)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return array_values($this->channels->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $keepThisChannel = true;
        foreach ($this->channels as $channel) {
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($channel->$getter() !== $value) {
                    $keepThisChannel = false;
                }
            }

            if ($keepThisChannel) {
                return $channel;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
