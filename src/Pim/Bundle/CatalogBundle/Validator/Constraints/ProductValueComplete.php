<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Complete constraint for ProductValue
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.5
 * @see Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteChecker
 */
class ProductValueComplete extends Constraint
{
    /**
     * @var string
     */
    public $messageComplete = 'This value should be complete';

    /**
     * @var string
     */
    public $messageNotNull  = 'This value should not be null';

    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * @return ChannelInterface
     */
    public function getChannel()
    {
        if (!$this->channel instanceof ChannelInterface) {
            throw new \LogicException(
                sprintf(
                    'Expecting $channel to be an instance of "Pim\Bundle\CatalogBundle\Model\ChannelInterface", ' .
                    'got "%s"',
                    is_object($this->channel) ? get_class($this->channel) : (string) $this->channel
                )
            );
        }

        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return array('channel');
    }
}
