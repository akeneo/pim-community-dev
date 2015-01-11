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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule definition normalizer for internal api
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleDefinitionNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['array'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'id'       => $object->getId(),
            'code'     => $object->getCode(),
            'type'     => $object->getType(),
            'priority' => $object->getPriority(),
            'content'  => $object->getContent(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleDefinitionInterface &&
            in_array($format, $this->supportedFormats);
    }
}
