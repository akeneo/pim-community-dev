<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel as ChannelConstraint;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Calculator\CompletenessCalculator;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Product reader
  *
  * @author    Gildas Quemener <gildas@akeneo.com>
  * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
  * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class ProductReader extends ORMReader
{
    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ChannelConstraint
     */
    protected $channel;

    /** @var Pim\Bundle\CatalogBundle\Doctrine\EntityRepository */
    protected $repository;

    /** @var ProductManager */
    protected $productManager;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var CompletenessCalculator */
    protected $calculator;

    /**
     * @param ProductManager         $productManager
     * @param ChannelManager         $channelManager
     * @param CompletenessCalculator $calculator
     */
    public function __construct(
        ProductManager $productManager,
        ChannelManager $channelManager,
        CompletenessCalculator $calculator
    ) {
        $this->productManager = $productManager;
        $this->channelManager = $channelManager;
        $this->calculator     = $calculator;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->query) {
            $channel = current($this->channelManager->getChannels(array('code' => $this->channel)));
            if (!$channel) {
                throw new \InvalidArgumentException(
                    sprintf('Could not find the channel %s', $this->channel)
                );
            }

            $this->calculator->calculateChannelCompleteness($channel);

            $this->query = $this->getProductRepository()
                ->buildByChannelAndCompleteness($channel)
                ->getQuery();
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

    protected function getProductRepository()
    {
        return $this->productManager->getFlexibleRepository();
    }
}
