<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\ChannelInterface;

/**
 * Class ChannelFactory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelFactory
{
    /** @var string */
    protected $channelClass;

    /**
     * @param string $channelClass
     */
    public function __construct($channelClass)
    {
        $this->channelClass = $channelClass;
    }

    /**
     * @return ChannelInterface
     */
    public function create()
    {
        return new $this->channelClass();
    }
}
