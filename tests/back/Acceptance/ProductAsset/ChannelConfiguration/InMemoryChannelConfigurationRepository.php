<?php

namespace AkeneoEnterprise\Test\Acceptance\ProductAsset\ChannelConfiguration;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\Asset\Component\Model\ChannelVariationsConfiguration;
use Akeneo\Asset\Component\Repository\ChannelConfigurationRepositoryInterface;

class InMemoryChannelConfigurationRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, ChannelConfigurationRepositoryInterface
{
    /** @var ArrayCollection */
    private $channelConfigurations;

    public function __construct(array $channelConfigurations = [])
    {
        $this->channelConfigurations = new ArrayCollection($channelConfigurations);
    }

    public function getIdentifierProperties()
    {
        return ['channel'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->channelConfigurations->get($identifier);
    }

    public function save($channelConfiguration, array $options = [])
    {
        if (!$channelConfiguration instanceof ChannelVariationsConfiguration) {
            throw new \InvalidArgumentException('Only chanel configuration are supported.');
        }

        $this->channelConfigurations->set($channelConfiguration->getChannel()->getCode(), $channelConfiguration);
    }

    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
