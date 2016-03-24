<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Twig;

use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface;

/**
 * Twig extension for rule presentation
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class RuleExtension extends \Twig_Extension
{
    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param PresenterRegistryInterface $presenterRegistry
     * @param LocaleResolver             $localeResolver
     */
    public function __construct(PresenterRegistryInterface $presenterRegistry, LocaleResolver $localeResolver)
    {
        $this->presenterRegistry = $presenterRegistry;
        $this->localeResolver    = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('present_rule_action_value', [$this, 'presentRuleActionValue']),
            new \Twig_SimpleFilter('append_locale_and_scope_context', [$this, 'appendLocaleAndScopeContext']),
        ];
    }

    /**
     * Transform the mixed input coming from a rule action to a string, using a presenter for localized attributes
     *
     * Example with a localized metric:
     * input: ['value' => 10, 'unit' => 'GRAM'], 'weight'
     * output: 10 Gram
     *
     * Example with a collection of localized prices:
     * input: [['data' => 10, 'currency' => 'EUR'], ['data' => 12, 'currency' => 'USD']], 'weight'
     * output: €10, $12
     *
     * Example with a non localized array:
     * input: ['foo', 'bar'], null
     * output: foo, bar
     *
     * Example with a file:
     * input: ['originalFilename' => 'image.jpg'], null
     * output: <i class="icon-file"></i> image.jpg
     *
     * @param mixed  $value
     * @param string $code
     *
     * @return string
     */
    public function presentRuleActionValue($value, $code)
    {
        $presenter = $this->presenterRegistry->getPresenterByAttributeCode($code);

        if (is_array($value)) {
            if (isset($value['originalFilename'])) {
                return sprintf('<i class="icon-file"></i> %s', $value['originalFilename']);
            }

            if (null !== $presenter) {
                $value = $presenter->present($value, ['locale' => $this->localeResolver->getCurrentLocale()]);

                return is_array($value) ? join(', ', $value) : $value;
            }

            foreach ($value as $index => $val) {
                $value[$index] = $this->presentRuleActionValue($val, $code);
            }

            return join(', ', $value);
        }

        if (null !== $presenter) {
            return $presenter->present($value, ['locale' => $this->localeResolver->getCurrentLocale()]);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string $locale
     * @param string $scope
     *
     * @return string
     */
    public function appendLocaleAndScopeContext($value, $locale = '', $scope = '')
    {
        $append = [];

        if ('' !== $locale) {
            $append[] = sprintf(
                '<i class="flag flag-%s"></i> %s',
                strtolower(preg_split('/_/', $locale)[1]),
                preg_split('/_/', $locale)[0]
            );
        }

        if ('' !== $scope) {
            $append[] = $scope;
        }

        if (!empty($append)) {
            $value .= sprintf(' [ %s ]', join(' | ', $append));
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_catalog_rule_rule_extension';
    }
}
