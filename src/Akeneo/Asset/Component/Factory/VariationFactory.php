<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;

/**
 * Variation factory
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class VariationFactory
{
    /** @var string */
    protected $variationClass;

    /**
     * @param string $variationClass
     */
    public function __construct($variationClass)
    {
        $this->variationClass = $variationClass;
    }

    /**
     * Create a Variation with the given Channel
     *
     * @param ChannelInterface|null $channel
     *
     * @return VariationInterface
     */
    public function create(ChannelInterface $channel = null)
    {
        $variation = new $this->variationClass();
        if (null !== $channel) {
            $variation->setChannel($channel);
        }

        return $variation;
    }
}
