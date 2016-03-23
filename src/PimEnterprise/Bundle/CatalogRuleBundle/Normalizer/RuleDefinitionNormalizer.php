<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Normalizer;

use Akeneo\Bundle\RuleEngineBundle\Normalizer\RuleDefinitionNormalizer as AkeneoRuleDefinitionNormalizer;
use Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize rule definition with content localized
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleDefinitionNormalizer extends AkeneoRuleDefinitionNormalizer implements NormalizerInterface
{
    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /**
     * @param PresenterRegistryInterface $presenterRegistry
     */
    public function __construct(PresenterRegistryInterface $presenterRegistry)
    {
        $this->presenterRegistry = $presenterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($ruleDefinition, $format = null, array $context = [])
    {
        $rule = parent::normalize($ruleDefinition, $format, $context);

        $rule['content'] = $this->localizeContent($ruleDefinition->getContent(), $context);

        return $rule;
    }

    /**
     * Localize content of Rule Definition
     *
     * @param array $content
     * @param array $context
     *
     * @return array
     */
    protected function localizeContent(array $content, array $context)
    {
        foreach ($content as $key => $items) {
            foreach ($items as $index => $action) {
                if (isset($action['field']) && isset($action['value'])) {
                    $presenter = $this->presenterRegistry->getPresenterByAttributeCode($action['field']);

                    if (null !== $presenter) {
                        $content[$key][$index]['value'] = $presenter->present($action['value'], $context);
                    }
                }
            }
        }

        return $content;
    }
}
