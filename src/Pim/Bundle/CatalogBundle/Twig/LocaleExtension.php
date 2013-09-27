<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    protected $container;

    /**
     * @param \Pim\Bundle\CatalogBundle\Helper\LocaleHelper $localeHelper
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'localized_label' => new \Twig_Function_Method($this, 'localizedLabel')
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
        return \Locale::getDisplayName($code, $this->container->get('request')->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_locale_extension';
    }
    
}
