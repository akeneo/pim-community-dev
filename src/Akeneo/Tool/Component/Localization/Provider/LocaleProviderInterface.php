<?php

namespace Akeneo\Tool\Component\Localization\Provider;

use Akeneo\Channel\Component\Model\LocaleInterface;

/**
 * Interface LocaleProviderInterface
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocaleProviderInterface
{
    /**
     * Return a set of locales
     *
     * @return LocaleInterface[]
     */
    public function getLocales();
}
