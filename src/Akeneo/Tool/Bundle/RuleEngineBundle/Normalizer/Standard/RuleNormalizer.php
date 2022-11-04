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
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionTranslationInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Transforms a RuleDefinition object to a normalized Rule (ie: content is replace by actions + conditions)
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($ruleDefinition, $format = null, array $context = [])
    {
        Assert::isInstanceOf($ruleDefinition, RuleDefinitionInterface::class);

        /** @var $ruleDefinition RuleDefinitionInterface */
        $data = [
            'code'       => $ruleDefinition->getCode(),
            'type'       => $ruleDefinition->getType(),
            'priority'   => $ruleDefinition->getPriority(),
            'enabled'    => $ruleDefinition->isEnabled(),
            'conditions' => [],
            'actions'    => [],
            'labels'     => [],
        ];

        $content = $ruleDefinition->getContent();
        if (isset($content['conditions'])) {
            $data['conditions'] = $content['conditions'];
        }
        if (isset($content['actions'])) {
            $data['actions'] = $content['actions'];
        }
        foreach ($ruleDefinition->getTranslations() as $translation) {
            /** @var $translation RuleDefinitionTranslationInterface */
            $data['labels'][$translation->getLocale()] = $translation->getLabel();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RuleDefinitionInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
