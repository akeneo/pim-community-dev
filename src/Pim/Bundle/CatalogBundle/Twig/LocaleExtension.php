<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

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
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var \Pim\Bundle\CatalogBundle\Helper\LocaleHelper
     */
    protected $localeHelper;

    /**
     * @param \Pim\Bundle\CatalogBundle\Helper\LocaleHelper $localeHelper
     */
    public function __construct(LocaleManager $localeManager, LocaleHelper $localeHelper)
    {
        $this->localeManager = $localeManager;
        $this->localeHelper  = $localeHelper;
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
        return $this->localeHelper->getLocalizedLabel($code, $this->localeManager->getUserLocaleCode());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_locale_extension';
    }
}
