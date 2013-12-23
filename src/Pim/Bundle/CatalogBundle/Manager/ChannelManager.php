<?php

namespace Pim\Bundle\CatalogBundle\Manager;

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
     * @return array
     */
    public function getChannels($criterias = array())
    {
        return $this
            ->objectManager
            ->getRepository('PimCatalogBundle:Channel')
            ->findBy($criterias);
    }

    /**
     * Get full channels with locales and currencies
     *
     * @return array
     */
    public function getFullChannels()
    {
        return $this
            ->objectManager
            ->getRepository('PimCatalogBundle:Channel')
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
     * @return Channel
     */
    public function getChannelByCode($code)
    {
        return $this
            ->objectManager
            ->getRepository('PimCatalogBundle:Channel')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Get channel choices with criterias
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @param array $criterias
     *
     * @return string[]
     */
    public function getChannelChoices($criterias = array())
    {
        $channels = $this->getChannels($criterias);

        $choices = array();
        foreach ($channels as $channel) {
            $choices[$channel->getCode()] = $channel->getLabel();
        }

        return $choices;
    }

    /**
     * Get channel choices with user channel code in first
     *
     * @return string[]
     *
     * @throws \Exception
     */
    public function getChannelChoiceWithUserChannel()
    {
        $channelChoices  = $this->getChannelChoices();
        $userChannelCode = $this->getUserChannelCode();
        if (!array_key_exists($userChannelCode, $channelChoices)) {
            throw new \Exception('User channel code is deactivated');
        }

        $userChannelValue = $channelChoices[$userChannelCode];
        $newChannelChoices = array($userChannelCode => $userChannelValue);
        unset($channelChoices[$userChannelCode]);

        return array_merge($newChannelChoices, $channelChoices);
    }

    /**
     * Get user channel code
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getUserChannelCode()
    {
        $user = $this->securityContext->getToken()->getUser();

        $catalogScope = $user->getCatalogScope();

        if (!$catalogScope) {
            throw new \Exception('User must have a catalog scope defined');
        }

        return $catalogScope->getCode();
    }
}
