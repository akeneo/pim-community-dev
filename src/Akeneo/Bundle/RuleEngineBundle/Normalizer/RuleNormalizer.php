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
 * Transforms a RuleDefinition object to a normalized Rule (ie: content is replace by actions + conditions)
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [
            'code'     => $object->getCode(),
            'type'     => $object->getType(),
            'priority' => $object->getPriority(),
        ];

        $content = $object->getContent();
        if (isset($content['conditions'])) {
            $data['conditions'] = $content['conditions'];
        }
        if (isset($content['actions'])) {
            $data['actions'] = $content['actions'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleDefinitionInterface;
    }
}
