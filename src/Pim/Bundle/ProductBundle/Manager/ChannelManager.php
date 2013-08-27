<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Common\Persistence\ObjectManager;

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
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $securityContext;

    /**
     * Constructor
     * @param ObjectManager   $objectManager   the storage manager
     * @param SecurityContext $securityContext the security context
     */
    public function __construct(ObjectManager $objectManager, SecurityContext $securityContext)
    {
        $this->objectManager = $objectManager;
        $this->securityContext = $securityContext;
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
        return $this->objectManager->getRepository('PimProductBundle:Channel')->findBy($criterias);
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

    /**
     * Get channel choices with user channel code in first
     *
     * @return multitype:string
     */
    public function getChannelChoiceWithUserChannel()
    {
        $channelChoices  = $this->getChannelChoices();
        $userChannelCode = $this->getUserChannelCode();
        $userChannelValue = $channelChoices[$userChannelCode];

        $newChannelChoices = array($userChannelCode => $userChannelValue);
        unset($channelChoices[$userChannelCode]);

        return array_merge($newChannelChoices, $channelChoices);
    }

    /**
     * Get user channel code
     *
     * @return string
     */
    public function getUserChannelCode()
    {
        $user = $this->securityContext->getToken()->getUser();

        return (string) $user->getValue('catalogscope');
    }
}
