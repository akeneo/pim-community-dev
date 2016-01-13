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
 * Chained denormalizer
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ChainedDenormalizer implements DenormalizerInterface
{
    /** @var DenormalizerInterface[] */
    protected $denormalizers = [];

    /**
     * @param DenormalizerInterface $denormalizer
     *
     * @return ChainedDenormalizer
     */
    public function addDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->denormalizers[] = $denormalizer;

        if ($denormalizer instanceof ChainedDenormalizerAwareInterface) {
            $denormalizer->setChainedDenormalizer($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->supportsDenormalization($data, $class, $format)) {
                return $denormalizer->denormalize($data, $class, $format, $context);
            }
        }

        throw new \LogicException('No denormalizer able to denormalize the data.');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->supportsDenormalization($data, $type, $format)) {
                return true;
            }
        }

        return false;
    }
}
