<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Normalizer;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionTranslationInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleRelationInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Rule relation normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleRelationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    protected ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($ruleRelation, $format = null, array $context = [])
    {
        Assert::isInstanceOf($ruleRelation, RuleRelationInterface::class);

        /** @var $ruleRelation RuleRelationInterface */
        return [
            'rule'      => $ruleRelation->getRuleDefinition()->getCode(),
            'attribute' => $this->doctrine->getManagerForClass($ruleRelation->getResourceName())
                ->find($ruleRelation->getResourceName(), $ruleRelation->getResourceId())->getCode(),
            'labels'    => $this->getRuleDefintionLabels($ruleRelation),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RuleRelationInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function getRuleDefintionLabels(RuleRelationInterface $ruleRelation): array
    {
        $result = [];
        foreach ($ruleRelation->getRuleDefinition()->getTranslations() as $translation) {
            /** @var RuleDefinitionTranslationInterface $translation */
            $result[$translation->getLocale()] = $translation->getLabel();
        }

        return $result;
    }
}
