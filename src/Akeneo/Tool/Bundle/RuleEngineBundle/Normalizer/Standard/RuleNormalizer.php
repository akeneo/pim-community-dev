<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\Standard;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transforms a RuleDefinition object to a normalized Rule (ie: content is replace by actions + conditions)
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            'code'       => $object->getCode(),
            'type'       => $object->getType(),
            'priority'   => $object->getPriority(),
            'conditions' => [],
            'actions'    => [],
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
