<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Twig;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
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

    /** @var FileInfoRepositoryInterface */
    private $fileInfoRepository;

    public function __construct(
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver,
        AttributeRepositoryInterface $attributeRepository,
        TranslatorInterface $translator,
        FileInfoRepositoryInterface $fileInfoRepository
    ) {
        $this->presenterRegistry   = $presenterRegistry;
        $this->localeResolver      = $localeResolver;
        $this->attributeRepository = $attributeRepository;
        $this->translator = $translator;
        $this->fileInfoRepository = $fileInfoRepository;
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
    public function presentRuleActionValue($value, $code): string
    {
        $presenter = $this->presenterRegistry->getPresenterByFieldCode($code);
        if (null === $presenter) {
            $presenter = $this->presenterRegistry->getPresenterByAttributeCode($code);
        }

        if (is_array($value)) {
            if (null !== $presenter) {
                $value = $presenter->present($value, ['locale' => $this->localeResolver->getCurrentLocale(), 'attribute' => $code]);

                return is_array($value) ? implode(', ', $value) : $value;
            }

            foreach ($value as $index => $val) {
                $value[$index] = $this->presentRuleActionValue($val, $code);
            }

            return implode(', ', $value);
        }

        $mediaCodes = $this->attributeRepository->findMediaAttributeCodes();
        if (in_array($code, $mediaCodes)) {
            $fileInfo = $this->fileInfoRepository->findOneByIdentifier($value);
            if (null !== $fileInfo) {
                return sprintf('<i class="icon-file"></i> %s', $fileInfo->getOriginalFilename());
            }

            return sprintf('<i class="icon-file"></i> %s', $value);
        }

        if (null !== $presenter) {
            return $presenter->present($value, ['locale' => $this->localeResolver->getCurrentLocale(), 'attribute' => $code]);
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
            $localeInfo = preg_split('/_/', $locale);
            $countryCode = $localeInfo[1];
            if (count($localeInfo) === 3) {
                $countryCode = $localeInfo[2];
            }
            $append[] = sprintf(
                '<i class="flag flag-%s"></i> %s',
                strtolower($countryCode),
                $localeInfo[0]
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
