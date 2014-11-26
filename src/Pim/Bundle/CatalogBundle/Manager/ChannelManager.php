<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Component\Resource\Model\SaverInterface;

/**
 * Channel manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelManager implements SaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ChannelRepository */
    protected $channelRepository;

    /** @var CompletenessManager */
    protected $completenessManager;

    /**
     * Constructor
     *
     * @param ObjectManager       $objectManager
     * @param ChannelRepository   $channelRepository
     * @param CompletenessManager $completenessManager
     */
    public function __construct(
        ObjectManager $objectManager,
        ChannelRepository $channelRepository,
        CompletenessManager $completenessManager
    ) {
        $this->objectManager = $objectManager;
        $this->channelRepository = $channelRepository;
        $this->completenessManager = $completenessManager;
    }

    /**
     * '@inheritdoc}
     */
    public function save($channel, array $options = [])
    {
        if (!$channel instanceof Channel) {
            throw new \InvalidArgumentException(
                sprintf('Expects a "Pim\Bundle\CatalogBundle\Entity\Channel", "%s" provided.', get_class($channel))
            );
        }

        $options = array_merge(['flush' => true, 'schedule' => true], $options);
        $this->objectManager->persist($channel);
        if (true === $options['schedule']) {
            $this->completenessManager->scheduleForChannel($channel);
        }
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Get channels with criterias
     *
     * @param array $criterias
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel[]
     */
    public function getChannels($criterias = array())
    {
        return $this->channelRepository->findBy($criterias);
    }

    /**
     * Get full channels with locales and currencies
     *
     * @return array
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
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    public function getChannelByCode($code)
    {
        return $this->channelRepository->findOneBy(array('code' => $code));
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

        $choices = array();
        foreach ($channels as $channel) {
            $choices[$channel->getCode()] = $channel->getLabel();
        }

        return $choices;
    }
}
