<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Normalizer;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Rule definition normalizer for internal api
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleCollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var string[] */
    protected $supportedFormats = array('array');

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $rules = [];

        foreach ($object as $rule) {
            $rules[] = $this->serializer->normalize($rule, $format, $context);
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ((is_array($data) && current($data) instanceof RuleDefinitionInterface) ||
            ($data instanceof Collection && $data->first() instanceof RuleDefinitionInterface)) &&
            in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
