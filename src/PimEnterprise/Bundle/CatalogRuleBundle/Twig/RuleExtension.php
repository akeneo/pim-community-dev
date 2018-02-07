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
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param PresenterRegistryInterface   $presenterRegistry
     * @param LocaleResolver               $localeResolver
     * @param AttributeRepositoryInterface $attributeRepository
     * @param TranslatorInterface          $translator
     */
    public function __construct(
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver,
        AttributeRepositoryInterface $attributeRepository,
        TranslatorInterface $translator
    ) {
        $this->presenterRegistry   = $presenterRegistry;
        $this->localeResolver      = $localeResolver;
        $this->attributeRepository = $attributeRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('present_rule_action_value', [$this, 'presentRuleActionValue']),
            new \Twig_SimpleFilter('append_locale_and_scope_context', [$this, 'appendLocaleAndScopeContext']),
            new \Twig_SimpleFilter('append_include_children_context', [$this, 'appendIncludeChildrenContext']),
        ];
    }

    /**
     * Transform the mixed input coming from a rule action to a string, using a presenter for localized attributes.
     * The value can be an attribute value, a field value, or an array of these values.
     *
     * Example with a localized metric:
     * input: ['amount' => 10, 'unit' => 'GRAM'], 'weight'
     * output: 10 Gram
     *
     * Example with a collection of localized prices:
     * input: [['amount' => 10, 'currency' => 'EUR'], ['amount' => 12, 'currency' => 'USD']], 'weight'
     * output: €10, $12
     *
     * Example with a non localized array:
     * input: ['foo', 'bar'], null
     * output: foo, bar
     *
     * Example with a file:
     * input: '/path/to/my/image.jpg', null
     * output: <i class="icon-file"></i> image.jpg
     *
     * @param mixed  $value
     * @param string $code
     *
     * @return string
     */
    public function presentRuleActionValue($value, $code)
    {
        $presenter = $this->presenterRegistry->getPresenterByFieldCode($code);
        if (null === $presenter) {
            $presenter = $this->presenterRegistry->getPresenterByAttributeCode($code);
        }

        if (is_array($value)) {
            if (null !== $presenter) {
                $value = $presenter->present($value, ['locale' => $this->localeResolver->getCurrentLocale()]);

                return is_array($value) ? implode(', ', $value) : $value;
            }

            foreach ($value as $index => $val) {
                $value[$index] = $this->presentRuleActionValue($val, $code);
            }

            return implode(', ', $value);
        }

        $mediaCodes = $this->attributeRepository->findMediaAttributeCodes();
        if (in_array($code, $mediaCodes)) {
            return sprintf('<i class="icon-file"></i> %s', basename($value));
        }

        if (null !== $presenter) {
            return $presenter->present($value, ['locale' => $this->localeResolver->getCurrentLocale()]);
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
            $value .= sprintf(' [ %s ]', implode(' | ', $append));
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string $field
     * @param bool $includeChildren
     *
     * @return string
     */
    public function appendIncludeChildrenContext($value, $field = '', $includeChildren = false): string
    {
        if ('categories' === $field && true === $includeChildren) {
            $locale = $this->localeResolver->getCurrentLocale();
            $value .= sprintf(' %s', $this->translator->trans(
                'pimee_catalog_rule.actions.options.include_children',
                [],
                null,
                $locale
            ));
        }

        return $value;
    }
}
