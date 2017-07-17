<?php

namespace Pim\Bundle\LocalizationBundle\Twig;

use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface;

/**
 * Twig extension to present localized attribute options
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionExtension extends \Twig_Extension
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
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'attribute_option_presenter',
                [$this, 'attributeOptionPresenter'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Presents an attribute option
     *
     * @param string $value
     * @param string $optionName
     *
     * @return string|null
     */
    public function attributeOptionPresenter($value, $optionName)
    {
        $presenter = $this->presenterRegistry->getAttributeOptionPresenter($optionName);
        if (null !== $presenter) {
            $locale = $this->localeResolver->getCurrentLocale();

            if (null !== $locale) {
                return $presenter->present($value, ['locale' => $locale]);
            }
        }

        return $value;
    }
}
