<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Not Blank constraint for ProductValue
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNotBlank extends Constraint
{
    /**
     * @var string
     */
    public $messageNotBlank = 'This value should not be blank';

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
        return ['channel'];
    }
}
