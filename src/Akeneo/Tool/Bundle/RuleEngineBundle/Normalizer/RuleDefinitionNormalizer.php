<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionTranslationInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Rule definition normalizer for internal api
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleDefinitionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormats = ['array', 'internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($ruleDefinition, $format = null, array $context = [])
    {
        Assert::isInstanceOf($ruleDefinition, RuleDefinitionInterface::class);

        /** @var $ruleDefinition RuleDefinitionInterface */
        return [
            'id'       => $ruleDefinition->getId(),
            'code'     => $ruleDefinition->getCode(),
            'type'     => $ruleDefinition->getType(),
            'priority' => $ruleDefinition->getPriority(),
            'enabled' => $ruleDefinition->isEnabled(),
            'content'  => $ruleDefinition->getContent(),
            'labels'   => $this->formatLabels($ruleDefinition),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RuleDefinitionInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function formatLabels(RuleDefinitionInterface $ruleDefinition): array
    {
        $result = [];
        foreach ($ruleDefinition->getTranslations() as $translation) {
            /** @var $translation RuleDefinitionTranslationInterface */
            $result[$translation->getLocale()] = $translation->getLabel();
        }

        return $result;
    }
}
