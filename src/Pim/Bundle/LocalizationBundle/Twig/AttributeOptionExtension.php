<?php

namespace Pim\Bundle\LocalizationBundle\Twig;

use Pim\Component\Localization\Presenter\PresenterRegistryInterface;
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
    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param PresenterRegistryInterface $presenterRegistry
     * @param RequestStack               $requestStack
     */
    public function __construct(PresenterRegistryInterface $presenterRegistry, RequestStack $requestStack)
    {
        $this->presenterRegistry = $presenterRegistry;
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
                'present_attribute_option',
                [$this, 'presentAttributeOption'],
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
    public function presentAttributeOption($value, $optionName)
    {
        $presenter = $this->presenterRegistry->getAttributeOptionPresenter($optionName);
        if (null !== $presenter) {
            $locale = $this->getLocale();

            if (null !== $locale) {
                return $presenter->present($value, ['locale' => $locale]);
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
