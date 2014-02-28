<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;

/**
 * Channel manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelManager
{
    /**
     * @var ChannelRepository $repository
     */
    protected $repository;

    /**
     * Constructor
     * @param ChannelRepository $repository
     */
    public function __construct(ChannelRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get channels with criterias
     *
     * @param array $criterias
     *
     * @return array
     */
    public function getChannels($criterias = array())
    {
        return $this->repository->findBy($criterias);
    }

    /**
     * Get full channels with locales and currencies
     *
     * @return array
     */
    public function getFullChannels()
    {
        return $this
            ->repository
            ->createQueryBuilder('ch')
            ->select('ch, lo, cu')
            ->leftJoin('ch.locales', 'lo')
            ->leftJoin('ch.currencies', 'cu')
            ->getQuery()
            ->getResult();
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
        return $this->repository->findOneBy(array('code' => $code));
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
