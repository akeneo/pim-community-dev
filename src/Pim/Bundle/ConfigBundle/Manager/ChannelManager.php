<?php
namespace Pim\Bundle\ConfigBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Channel manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelManager
{

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get channels with criterias
     *
     * @param multitype:string $criterias
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getChannels($criterias = array())
    {
        return $this->objectManager->getRepository('PimConfigBundle:Channel')->findBy($criterias);
    }

    /**
     * Get channel choices with criterias
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @param multitype $criterias
     *
     * @return multitype:string
     */
    public function getChannelChoices($criterias = array())
    {
        $channels = $this->getChannels($criterias);

        $choices = array();
        foreach ($channels as $channel) {
            $choices[$channel->getCode()] = $channel->getName();
        }

        return $choices;
    }
}
