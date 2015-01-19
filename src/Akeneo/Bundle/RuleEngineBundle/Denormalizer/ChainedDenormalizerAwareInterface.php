<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Denormalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Chained denormalizer aware interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ChainedDenormalizerAwareInterface
{
    public function setChainedDenormalizer(DenormalizerInterface $denormalizer);
}
