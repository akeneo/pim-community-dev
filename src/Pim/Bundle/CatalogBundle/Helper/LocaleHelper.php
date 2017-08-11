<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Intl;

/**
 * LocaleHelper essentially allow to translate locale code to localized locale label
 *
 * Static locales are not initialized on the constructor because
 * when LocaleHelper is constructed, the user is not yet initialized
 * and by the way don't have locale code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleHelper
{
    /** @var UserContext */
    protected $userContext;

    /** @var LocaleRepositoryInterface*/
    protected $localeRepository;

    /**
     * Constructor
     *
     * @param UserContext               $userContext
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(UserContext $userContext, LocaleRepositoryInterface $localeRepository)
    {
        $this->userContext = $userContext;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Returns the label of a locale in the specified language
     *
     * @param string $code        the code of the locale to translate
     * @param string $translateIn the locale in which the label should be translated (if null, user locale will be used)
     *
     * @return string
     */
    public function getLocaleLabel($code, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->userContext->getCurrentLocale()->getCode();

        return \Locale::getDisplayName($code, $translateIn);
    }
}
