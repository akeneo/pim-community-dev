<?php

namespace Pim\Bundle\LocalizationBundle\Twig;

use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Twig extension to present localized attribute options
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionExtension extends \Twig_Extension
{
    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param LocalizerRegistryInterface $localizerRegistry
     * @param RequestStack               $requestStack
     */
    public function __construct(LocalizerRegistryInterface $localizerRegistry, RequestStack $requestStack)
    {
        $this->localizerRegistry = $localizerRegistry;
        $this->requestStack      = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_localization.twig.attribute_option_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'localize_attribute_option',
                [$this, 'localizeAttributeOption'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Localize an attribute option
     *
     * @param string $value
     * @param string $optionName
     *
     * @return string|null
     */
    public function localizeAttributeOption($value, $optionName)
    {
        $localizer = $this->localizerRegistry->getAttributeOptionLocalizer($optionName);
        if (null !== $localizer) {
            $locale = $this->getLocale();

            if (null !== $locale) {
                return $localizer->convertDefaultToLocalizedFromLocale($value, $locale);
            }
        }

        return $value;
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
}
