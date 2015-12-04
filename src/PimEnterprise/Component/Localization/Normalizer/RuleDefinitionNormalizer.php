<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Localization\Normalizer;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterRegistryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule definition normalizer with localization of attribute values
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class RuleDefinitionNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['array'];

    /** @var NormalizerInterface */
    protected $ruleNormalizer;

    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param NormalizerInterface        $ruleNormalizer
     * @param PresenterRegistryInterface $presenterRegistry
     * @param LocaleResolver             $localeResolver
     */
    public function __construct(
        NormalizerInterface $ruleNormalizer,
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver
    ) {
        $this->ruleNormalizer    = $ruleNormalizer;
        $this->presenterRegistry = $presenterRegistry;
        $this->localeResolver    = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($ruleDefinition, $format = null, array $context = [])
    {
        $ruleDefinition = $this->ruleNormalizer->normalize($ruleDefinition, $format, $context);

        $ruleDefinition['content'] = $this->convertContent($ruleDefinition['content']);

        return $ruleDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleDefinitionInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Convert a RuleDefinition content
     *
     * @param array $content
     *
     * @return array
     */
    protected function convertContent(array $content)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        foreach ($content as $key => $items) {
            foreach ($items as $index => $action) {
                if (isset($action['field']) && isset($action['value'])) {
                    $presenter = $this->presenterRegistry->getPresenterByAttributeCode($action['field']);

                    if (null !== $presenter) {
                        $content[$key][$index]['value'] = $presenter->present($action['value'], $options);
                    }
                }
            }
        }

        return $content;
    }
}
