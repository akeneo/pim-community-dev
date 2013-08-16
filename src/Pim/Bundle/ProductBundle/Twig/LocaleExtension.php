<?php

namespace Pim\Bundle\ProductBundle\Twig;

use Pim\Bundle\ProductBundle\Helper\LocaleHelper;

/**
 * Twig extension to render locales from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleExtension extends \Twig_Extension
{
    /**
     * @var \Pim\Bundle\ProductBundle\Helper\LocaleHelper
     */
    protected $localeHelper;

    /**
     * @param \Pim\Bundle\ProductBundle\Helper\LocaleHelper $localeHelper
     */
    public function __construct(LocaleHelper $localeHelper)
    {
        $this->localeHelper = $localeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'localizedLabel' => new \Twig_Function_Method($this, 'localizedLabel')
        );
    }

    /**
     * Get displayed locale from locale code
     *
     * @param string $code
     *
     * @return string
     */
    public function localizedLabel($code)
    {
        return $this->localeHelper->getLocalizedLabel($code);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_locale_extension';
    }
}
