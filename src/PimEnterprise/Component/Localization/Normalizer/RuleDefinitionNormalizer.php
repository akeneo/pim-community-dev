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
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Pim\Component\Localization\Provider\Format\DateFormatProvider;
use Pim\Component\Localization\Provider\Format\NumberFormatProvider;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /** @var LocalizedAttributeConverterInterface */
    protected $converter;

    /** @var RequestStack */
    protected $requestStack;

    /** @var NumberFormatProvider */
    protected $numberFormatProvider;

    /** @var DateFormatProvider */
    protected $dateFormatProvider;

    /**
     * @param NormalizerInterface                  $ruleNormalizer
     * @param LocalizedAttributeConverterInterface $converter
     * @param RequestStack                         $requestStack
     * @param NumberFormatProvider                 $numberFormatProvider
     * @param DateFormatProvider                   $dateFormatProvider
     */
    public function __construct(
        NormalizerInterface $ruleNormalizer,
        LocalizedAttributeConverterInterface $converter,
        RequestStack $requestStack,
        NumberFormatProvider $numberFormatProvider,
        DateFormatProvider $dateFormatProvider
    ) {
        $this->ruleNormalizer       = $ruleNormalizer;
        $this->converter            = $converter;
        $this->requestStack         = $requestStack;
        $this->numberFormatProvider = $numberFormatProvider;
        $this->dateFormatProvider   = $dateFormatProvider;
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
     * @param  mixed $content
     *
     * @return mixed
     */
    protected function convertContent($content)
    {
        $localeOptions = $this->getLocaleOptions();

        foreach ($content as $key => $items) {
            foreach ($content[$key] as $index => $action) {
                $localizedAction = $this->converter->convertDefaultToLocalizedValue(
                    $action['field'],
                    $action['value'],
                    $localeOptions
                );
                $content[$key][$index]['value'] = $localizedAction;
            }
        }

        return $content;
    }

    /**
     * Returns current user locale.
     *
     * @return string|null
     */
    protected function getLocale()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        return $request->getLocale();
    }

    /**
     * Returns the options for the localizers
     *
     * @return array
     */
    protected function getLocaleOptions()
    {
        $locale = $this->getLocale();

        $numberOptions = $this->numberFormatProvider->getFormat($locale);
        $dateOptions = ['date_format' => $this->dateFormatProvider->getFormat($locale)];

        return array_merge($numberOptions, $dateOptions);
    }
}
