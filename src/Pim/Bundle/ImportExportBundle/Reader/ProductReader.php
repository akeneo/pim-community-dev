<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\ConfigBundle\Manager\ChannelManager;

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
    protected $channel;

    /**
     * @param EntityManager $em
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
            $this->query = $em
                ->getRepository('PimProductBundle:Product')
                ->buildByScope($this->channel);
        }

        return parent::read();
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    public function getChannel()
    {
        return $this->channel;
    }

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
