<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\ConfigBundle\Manager\ChannelManager;
use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\ProductBundle\Manager\ProductManager;

/**
 * Product reader
  *
  * @author    Gildas Quemener <gildas.quemener@gmail.com>
  * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
  * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class ProductReader extends ORMReader
{
    protected $em;

    /**
     * @Assert\NotBlank
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
     * {@inheritDoc}
     */
    public function read()
    {
        if (!$this->query) {
            $this->query = $this->repository
                ->buildByScope($this->channel)
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
