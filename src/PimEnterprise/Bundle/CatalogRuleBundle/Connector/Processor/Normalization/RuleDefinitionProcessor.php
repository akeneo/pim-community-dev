<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms rules definition to array of rules
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleDefinitionProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $ruleNormalizer;

    /**
     * @param SerializerInterface $serializer
     * @param LocaleManager       $localeManager
     * @param NormalizerInterface $ruleNormalizer
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleManager $localeManager,
        NormalizerInterface $ruleNormalizer
    ) {
        parent::__construct($serializer, $localeManager);

        $this->ruleNormalizer = $ruleNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $rules = [];
        foreach ($item as $ruleDefinition) {
            $normalizedRule = $this->ruleNormalizer->normalize($ruleDefinition);

            unset($normalizedRule['code']);
            unset($normalizedRule['type']);

            $rules[$ruleDefinition->getCode()] = $normalizedRule;
        }

        return ['rules' => $rules];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
