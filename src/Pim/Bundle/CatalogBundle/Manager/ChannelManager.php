<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

/**
 * Channel manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelManager
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CompletenessManager */
    protected $completenessManager;

    /**
     * Constructor
     *
     * @param ObjectManager              $objectManager
     * @param ChannelRepositoryInterface $channelRepository
     * @param CompletenessManager        $completenessManager
     */
    public function __construct(
        ObjectManager $objectManager,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager
    ) {
        $this->objectManager       = $objectManager;
        $this->channelRepository   = $channelRepository;
        $this->completenessManager = $completenessManager;
    }

    /**
     * Get channels with criterias
     *
     * @param array $criterias
     *
     * @return ChannelInterface[]
     */
    public function getChannels($criterias = [])
    {
        return $this->channelRepository->findBy($criterias);
    }

    /**
     * Get full channels with locales and currencies
     *
     * @return ChannelInterface[]
     */
    public function getFullChannels()
    {
        return $this->channelRepository->getFullChannels();
    }

    /**
     * Get channel by code
     *
     * @param string $code
     *
     * @return ChannelInterface
     */
    public function getChannelByCode($code)
    {
        return $this->channelRepository->findOneBy(['code' => $code]);
    }

    /**
     * Get channel choices
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @return string[]
     */
    public function getChannelChoices()
    {
        $channels = $this->getChannels();

        $choices = [];
        foreach ($channels as $channel) {
            $choices[$channel->getCode()] = $channel->getLabel();
        }

        return $choices;
    }
}
