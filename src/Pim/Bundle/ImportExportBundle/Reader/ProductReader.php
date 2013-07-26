<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\ConfigBundle\Manager\ChannelManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product reader
  *
  * @author    Gildas Quemener <gildas.quemener@gmail.com>
  * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
  * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class ProductReader extends ORMCursorReader
{
    protected $em;

    /**
     * @Assert\NotBlank(groups={"Configuration"})
     */
    protected $channel;

    /**
     * @param EntityManager  $em
     * @param ChannelManager $channelManager
     */
    public function __construct(EntityManager $em, ChannelManager $channelManager)
    {
        $this->em = $em;
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        if (!$this->query) {
            $this->query = $this->em
                ->getRepository('PimProductBundle:Product')
                ->buildByScope($this->channel);
        }

        return parent::read();
    }

    /**
     * Set channel
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Scoped products';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'channel' => array(
                'type' => 'choice',
                'options' => array(
                    'choices' => $this->channelManager->getChannelChoices()
                )
            )
        );
    }
}
