<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Product reader
  *
  * @author    Gildas Quemener <gildas.quemener@gmail.com>
  * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
  * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class ProductReader extends ORMReader
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @Assert\NotBlank
     * @Channel
     */
    protected $channel;

    /**
     * @param ProductManager $productManager
     * @param ChannelManager $channelManager
     */
    public function __construct(ProductManager $productManager, ChannelManager $channelManager)
    {
        $this->repository     = $productManager->getFlexibleRepository();
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function read(StepExecution $stepExecution)
    {
        if (!$this->query) {
            $channel = current($this->channelManager->getChannels(array('code' => $this->channel)));
            $this->query = $this->repository
                ->buildByChannelAndCompleteness($channel)
                ->getQuery();
        }

        return parent::read($stepExecution);
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
    public function getConfigurationFields()
    {
        return array(
            'channel' => array(
                'type' => 'choice',
                'options' => array(
                    'choices' => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            )
        );
    }
}
