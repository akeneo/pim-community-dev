<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Complete constraint for ProductValue
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var Channel
     */
    protected $channel;

    /**
     * @return Channel
     */
    public function getChannel()
    {
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
